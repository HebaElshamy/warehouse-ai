<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\InventoryCurrent;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'inventoryCurrent'])
            ->latest()
            ->get();

        return view('dashboard.inventory.index', compact('products'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'current_stock' => 'required|integer|min:0',
        ]);

        $currentStock = (int) $request->current_stock;

        $status = ($currentStock <= (int) $product->reorder_level) ? 'LOW' : 'OK';

        InventoryCurrent::updateOrCreate(
            ['product_id' => $product->id],
            [
                'current_stock' => $currentStock,
                'status' => $status,
                'last_updated_source' => 'MANUAL',
                'last_seen_at' => now(),
            ]
        );

        if ($status === 'LOW') {
            Alert::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'LOW_STOCK',
                    'is_resolved' => false,
                ],
                [
                    'message' => 'Low stock for ' . $product->product_name . ' (stock=' . $currentStock . ')',
                ]
            );
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory updated successfully');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|min:3',
        ]);

        $prefix = substr($request->barcode, 0, 3);

        $product = Product::where('barcode_prefix', $prefix)->first();

       if (!$product) {

                \App\Models\Alert::create([
                    'product_id' => null,
                    'type' => 'UNKNOWN_PREFIX',
                    'message' => 'Scanned barcode prefix not found: ' . $prefix,
                    'is_resolved' => false,
                ]);

                return redirect()->route('inventory.index')
                 ->withErrors(['barcode' => 'Product not found (prefix: '.$prefix.')']);
        }

        $inventory = InventoryCurrent::firstOrCreate(
            ['product_id' => $product->id],
            [
                'current_stock' => 0,
                'status' => 'OK',
                'last_updated_source' => 'SCAN',
                'last_seen_at' => now(),
            ]
        );

        $inventory->current_stock += 1;

        $inventory->status = ($inventory->current_stock <= (int) $product->reorder_level) ? 'LOW' : 'OK';
        $inventory->last_updated_source = 'SCAN';
        $inventory->last_seen_at = now();
        $inventory->save();

        if ($inventory->status === 'LOW') {
            Alert::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'LOW_STOCK',
                    'is_resolved' => false,
                ],
                [
                    'message' => 'Low stock for ' . $product->product_name . ' (stock=' . $inventory->current_stock . ')',
                ]
            );
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Stock increased via scan');
    }
}
