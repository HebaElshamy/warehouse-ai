<?php

namespace App\Models;

use App\Models\Alert;
use App\Models\Category;
use App\Models\InventoryCurrent;
use App\Models\Prediction;
use App\Models\SalesReference;
use App\Models\ScanItem;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];



    public function inventoryCurrent()
    {
        return $this->hasOne(InventoryCurrent::class, 'product_id');
    }

    public function scanItems()
    {
        return $this->hasMany(ScanItem::class, 'product_id');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'product_id');
    }

    public function salesReference()
    {
        return $this->hasOne(SalesReference::class, 'product_id');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'product_id');
    }

    public function category()
    {
         return $this->belongsTo(Category::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

public function getStockStateAttribute(): string
{
    $stock = (int) optional($this->inventoryCurrent)->current_stock;

    if ($stock <= 0) {
        return 'Backordered';
    }

    if ($stock <= (int) $this->reorder_level) {
        return 'Backordered';
    }

    return 'Active';
}
public function salesDailies()
{
    return $this->hasMany(\App\Models\SalesDaily::class);
}


}
