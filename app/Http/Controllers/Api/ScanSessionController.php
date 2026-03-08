<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScanEvent;
use App\Models\ScanItem;
use App\Models\Product;
use App\Models\InventoryCurrent;
use App\Models\Alert;
use App\Models\SalesDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanSessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
            'scanned_at' => 'required|integer',
            'barcodes' => 'required|array|min:1',
            'barcodes.*' => 'required|string|min:3|max:255',
        ]);

        $deviceId = $request->device_id;
        $scanTime = Carbon::createFromTimestamp($request->scanned_at);
        $saleDate = $scanTime->toDateString();

        // unique by FULL barcode
        $barcodes = array_values(array_unique(array_map('trim', $request->barcodes)));

        // last session (for diff) - tie-break with id
        $lastEvent = ScanEvent::where('device_id', $deviceId)
            ->orderByDesc('scanned_at')
            ->orderByDesc('id')
            ->first();

        // helper: barcode -> product by prefix (first 3)
        $mapBarcodeToProduct = function (string $barcode) {
            $prefix = substr($barcode, 0, 3);

            return Product::where('barcode_prefix', $prefix)
                ->where('is_active', true)
                ->first();
        };

        $unknownBarcodes = [];
        $barcodeToProductId = [];

        foreach ($barcodes as $bc) {
            $product = $mapBarcodeToProduct($bc);
            $barcodeToProductId[$bc] = $product?->id;

            if (!$product) {
                $unknownBarcodes[] = $bc;
            }
        }

        // prev barcodes for response diff only (optional)
        $prevBarcodes = [];
        if ($lastEvent) {
            $prevBarcodes = ScanItem::where('scan_event_id', $lastEvent->id)
                ->pluck('barcode')
                ->map(fn($v) => trim((string)$v))
                ->unique()
                ->values()
                ->all();
        }

        $added = array_values(array_diff($barcodes, $prevBarcodes));
        $removed = array_values(array_diff($prevBarcodes, $barcodes));

        DB::transaction(function () use (
            $deviceId,
            $scanTime,
            $saleDate,
            $barcodes,
            $barcodeToProductId,
            $unknownBarcodes,
            $lastEvent
        ) {
            // 1) Create scan event
            $event = ScanEvent::create([
                'device_id' => $deviceId,
                'scanned_at' => $scanTime,
                'total_barcodes' => count($barcodes),
                'raw_payload' => ['barcodes' => $barcodes],
            ]);

            // 2) Create scan items
            foreach ($barcodes as $bc) {
                ScanItem::create([
                    'scan_event_id' => $event->id,
                    'barcode' => $bc,
                    'product_id' => $barcodeToProductId[$bc] ?? null,
                    'is_unknown' => empty($barcodeToProductId[$bc]),
                ]);
            }

            // 3) Alerts for unknown barcodes
            foreach ($unknownBarcodes as $bc) {
                $prefix = substr($bc, 0, 3);

                Alert::create([
                    'product_id' => null,
                    'type' => 'UNKNOWN_PREFIX',
                    'message' => 'Unknown/Inactive prefix scanned: ' . $prefix . ' (barcode=' . $bc . ')',
                    'is_resolved' => false,
                ]);
            }

            // 4) Current counts by product (this event)
            $currentCounts = ScanItem::where('scan_event_id', $event->id)
                ->whereNotNull('product_id')
                ->selectRaw('product_id, COUNT(*) as c')
                ->groupBy('product_id')
                ->pluck('c', 'product_id')
                ->map(fn($v) => (int)$v)
                ->toArray();

            // 5) Prev counts by product (last event)
            $prevCounts = [];
            if ($lastEvent) {
                $prevCounts = ScanItem::where('scan_event_id', $lastEvent->id)
                    ->whereNotNull('product_id')
                    ->selectRaw('product_id, COUNT(*) as c')
                    ->groupBy('product_id')
                    ->pluck('c', 'product_id')
                    ->map(fn($v) => (int)$v)
                    ->toArray();
            }

            // 6) Inventory SNAPSHOT for union(prev,current)
            $allProductIds = array_values(array_unique(array_merge(array_keys($prevCounts), array_keys($currentCounts))));

            foreach ($allProductIds as $pid) {
                $product = Product::find($pid);
                if (!$product) continue;

                $stockNow = (int)($currentCounts[$pid] ?? 0);
                $status = ($stockNow <= (int)$product->reorder_level) ? 'LOW' : 'OK';

                InventoryCurrent::updateOrCreate(
                    ['product_id' => $pid],
                    [
                        'current_stock' => $stockNow,
                        'status' => $status,
                        'last_updated_source' => 'SCAN',
                        'last_seen_at' => $scanTime,
                    ]
                );

                if ($status === 'LOW') {
                    Alert::firstOrCreate(
                        [
                            'product_id' => $pid,
                            'type' => 'LOW_STOCK',
                            'is_resolved' => false,
                        ],
                        [
                            'message' => 'Low stock for ' . $product->product_name . ' (stock=' . $stockNow . ')',
                        ]
                    );
                }
            }

            // 7) ✅ SALES from delta: soldQty = prev - current (if positive)
            foreach ($allProductIds as $pid) {
                $prevQty = (int)($prevCounts[$pid] ?? 0);
                $currQty = (int)($currentCounts[$pid] ?? 0);

                $soldQty = $prevQty - $currQty;
                if ($soldQty <= 0) continue;

                $product = Product::find($pid);
                if (!$product) continue;

                $unitPrice = (float)($product->unit_price ?? 0);
                $amount = $soldQty * $unitPrice;

                $daily = SalesDaily::firstOrCreate(
                    ['product_id' => $pid, 'sale_date' => $saleDate],
                    ['sales_volume' => 0, 'sales_amount' => 0]
                );

                // increment safely
                $daily->increment('sales_volume', $soldQty);
                $daily->increment('sales_amount', $amount);
            }

            // 8) ✅ Update monthly reference (last 30 days) like your seeder
            $from = $scanTime->copy()->subDays(29)->toDateString();
            $to   = $scanTime->toDateString();

            foreach ($allProductIds as $pid) {
                $monthlySum = (float) SalesDaily::where('product_id', $pid)
                    ->whereBetween('sale_date', [$from, $to])
                    ->sum('sales_volume');

                DB::table('sales_references')->updateOrInsert(
                    ['product_id' => $pid],
                    [
                        'sales_volume_monthly' => $monthlySum,
                        'source' => 'SCAN',
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }
        });

        $uniqueProductIds = array_values(array_unique(array_filter(array_values($barcodeToProductId))));

        return response()->json([
            'ok' => true,
            'device_id' => $deviceId,
            'total_unique_barcodes' => count($barcodes),
            'unique_barcodes' => $barcodes,
            'unique_products_count' => count($uniqueProductIds),
            'unique_product_ids' => $uniqueProductIds,
            'diff' => [
                'added_barcodes' => $added,
                'removed_barcodes' => $removed,
            ],
            'unknown_barcodes' => $unknownBarcodes,
        ]);
    }
}
