<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $guarded = [];

    protected $casts = [
        'measured_at' => 'datetime',
    ];
}
