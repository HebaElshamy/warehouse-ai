<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category','supplier','inventoryCurrent'])->latest()->get();
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('dashboard.products.index', compact('products', 'categories', 'suppliers'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode_prefix' => ['required', 'digits:3', 'unique:products,barcode_prefix'],
            'product_name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0',
            'expiration_date' => 'nullable|date',
            'warehouse_location' => 'nullable|string|max:255',
            'reorder_level' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'barcode_prefix' => $request->barcode_prefix,
            'product_name' => $request->product_name,
            'category_id' => $request->category_id ?: null,
            'supplier_id' => $request->supplier_id ?: null,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'expiration_date' => $request->expiration_date,
            'warehouse_location' => $request->warehouse_location,
            'reorder_level' => (int) $request->reorder_level,
            'reorder_quantity' => (int) $request->reorder_quantity,
            'is_active' => $request->has('is_active'),
        ]);

        $product->inventoryCurrent()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'current_stock' => 0,
                'status' => 'OK',
                'last_updated_source' => 'MANUAL',
            ]
        );

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully');
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'barcode_prefix' => [
                'required',
                'digits:3',
                'unique:products,barcode_prefix,' . $product->id,
            ],
            'product_name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0',
            'expiration_date' => 'nullable|date',
            'warehouse_location' => 'nullable|string|max:255',
            'reorder_level' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:0',
        ]);

        $product->update([
            'barcode_prefix' => $request->barcode_prefix,
            'product_name' => $request->product_name,
            'category_id' => $request->category_id ?: null,
            'supplier_id' => $request->supplier_id ?: null,
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'expiration_date' => $request->expiration_date,
            'warehouse_location' => $request->warehouse_location,
            'reorder_level' => (int) $request->reorder_level,
            'reorder_quantity' => (int) $request->reorder_quantity,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(string $id)
    {
        Product::findOrFail($id)->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }
}
