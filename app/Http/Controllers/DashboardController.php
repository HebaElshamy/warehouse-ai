<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryCurrent;
use App\Models\SalesDaily;
use App\Models\Prediction;
use App\Models\Alert;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();

        // Low stock: include products with no inventory_current => stock=0
        $lowStockProducts = Product::with('inventoryCurrent')
            ->get()
            ->filter(function ($product) {
                $stock = $product->inventoryCurrent?->current_stock ?? 0;
                return $stock <= (int)$product->reorder_level;
            });

        $lowStockCount = $lowStockProducts->count();

        // ===== Total Sales (Last 30 days) =====
        $from = now()->subDays(29)->toDateString();
        $to   = now()->toDateString();

        $salesTotals = SalesDaily::whereBetween('sale_date', [$from, $to])
            ->selectRaw('COALESCE(SUM(sales_volume),0) as qty, COALESCE(SUM(sales_amount),0) as amount')
            ->first();

        $totalSalesQty = (int)($salesTotals->qty ?? 0);
        $totalSalesAmount = (float)($salesTotals->amount ?? 0);

        // ===== Sales chart (Last 30 days) =====
        $salesTrend = SalesDaily::whereBetween('sale_date', [$from, $to])
            ->selectRaw('sale_date, SUM(sales_volume) as qty, SUM(sales_amount) as amount')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->map(fn($r) => [
                'date' => (string)$r->sale_date,
                'qty' => (int)$r->qty,
                'amount' => (float)$r->amount,
            ]);

        // ===== Forecast next week (latest batch) =====
        $latestGeneratedAt = Prediction::max('generated_at');

        $forecastSuggestedTotal = 0;
        $forecastDemandTotal = 0;

        if ($latestGeneratedAt) {
            $forecast = Prediction::where('generated_at', $latestGeneratedAt)
                ->where('horizon_days', 7)
                ->selectRaw('COALESCE(SUM(suggested_order_qty),0) as suggested, COALESCE(SUM(predicted_demand),0) as demand')
                ->first();

            $forecastSuggestedTotal = (int)($forecast->suggested ?? 0);
            $forecastDemandTotal = (float)($forecast->demand ?? 0);
        }

        // ===== Alerts box (unresolved) =====
        $alerts = Alert::with('product')
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockCount',
            'totalSalesQty',
            'totalSalesAmount',
            'forecastSuggestedTotal',
            'forecastDemandTotal',
            'salesTrend',
            'alerts',
            'lowStockProducts',
            'latestGeneratedAt'
        ));
    }
}
