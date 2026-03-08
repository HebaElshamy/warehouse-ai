<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDaily extends Model
{
    protected $guarded = [];

   
    protected $casts = [
    'sale_date' => 'date',
    'sales_amount' => 'decimal:2',
];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
