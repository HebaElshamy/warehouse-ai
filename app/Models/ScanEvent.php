<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw_payload' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ScanItem::class, 'scan_event_id');
    }
}
