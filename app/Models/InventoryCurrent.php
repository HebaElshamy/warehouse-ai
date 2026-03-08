<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCurrent extends Model
{
    protected $guarded = [];
    protected $table = 'inventory_current';
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'int';

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
