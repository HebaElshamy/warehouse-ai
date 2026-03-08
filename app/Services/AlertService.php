<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Product;

class AlertService
{
    public function checkLowStock(Product $product): void
    {
        $reorderLevel = (int) ($product->reorder_level ?? 0);

        // لازم يكون عندك relation اسمها inventoryCurrent على Product
        $currentStock = (int) optional($product->inventoryCurrent)->current_stock;

        if ($currentStock <= $reorderLevel) {
            $this->createIfNotExists(
                $product->id,
                'LOW_STOCK',
                'HIGH',
                "Low stock: {$product->name} current_stock={$currentStock} <= reorder_level={$reorderLevel}"
            );
        }
    }

    public function checkForecastRisk(Product $product): void
    {
        // لازم يكون عندك relation اسمها predictions على Product
        $latestPrediction = $product->predictions()->latest()->first();
        $suggestedQty = (int) ($latestPrediction->suggested_order_qty ?? 0);

        if ($suggestedQty > 0) {
            $this->createIfNotExists(
                $product->id,
                'FORECAST_RISK',
                'MEDIUM',
                "Forecast risk: {$product->name} suggested_order_qty={$suggestedQty}"
            );
        }
    }

    public function generateAlertsForProduct(Product $product): void
    {
        $this->checkLowStock($product);
        $this->checkForecastRisk($product);
    }

    private function createIfNotExists(?int $productId, string $type, string $severity, string $message): void
    {
        $exists = Alert::where('product_id', $productId)
            ->where('type', $type)
            ->where('is_resolved', false)
            ->exists();

        if ($exists) return;

        Alert::create([
            'product_id'  => $productId,
            'type'        => $type,
            'severity'    => $severity,
            'message'     => $message,
            'is_resolved' => false,
            'resolved_at' => null,
        ]);
    }
}
