<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Product;
use App\Models\Prediction;
use App\Models\SalesDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ForecastController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('inventoryCurrent')->get();

        $selectedProduct = null;
        $sales = collect();
        $latestPrediction = null;

        $horizon = (int) $request->get('horizon_days', 7);

        if ($request->filled('product_id')) {

            $selectedProduct = Product::with('inventoryCurrent')
                ->find($request->product_id);

            if ($selectedProduct) {

                $sales = SalesDaily::where('product_id', $selectedProduct->id)
                    ->orderByDesc('sale_date')
                    ->take(60)
                    ->get();

                $latestPrediction = Prediction::where('product_id', $selectedProduct->id)
                    ->where('model_version', 'rf_v1')
                    ->where('horizon_days', $horizon)
                    ->orderByDesc('generated_at')
                    ->first();
            }
        }

        return view('dashboard.forecasting.index', compact(
            'products',
            'selectedProduct',
            'sales',
            'latestPrediction',
            'horizon'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'horizon_days' => 'required|integer|min:1|max:30',
        ]);

        $productId = (int) $request->product_id;
        $horizon = (int) $request->horizon_days;

        Artisan::call('forecast:rf', [
            '--horizon' => $horizon,
        ]);

        $prediction = Prediction::where('product_id', $productId)
            ->where('model_version', 'rf_v1')
            ->where('horizon_days', $horizon)
            ->orderByDesc('generated_at')
            ->first();

        if ($prediction && (int)$prediction->suggested_order_qty > 0) {
            Alert::firstOrCreate(
                [
                    'product_id' => $productId,
                    'type' => 'FORECAST',
                    'is_resolved' => false,
                ],
                [
                    'message' => 'Forecast suggests ordering ' . (int)$prediction->suggested_order_qty . ' units (horizon ' . $horizon . ' days)',
                ]
            );
        }

        return redirect()
            ->route('forecasting.index', [
                'product_id' => $productId,
                'horizon_days' => $horizon,
            ])
            ->with('success', 'RandomForest Forecast generated successfully.');
    }
}
