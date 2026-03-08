<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanItem extends Model
{
    protected $guarded = [];

    public function scanEvent()
    {
        return $this->belongsTo(ScanEvent::class, 'scan_event_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
