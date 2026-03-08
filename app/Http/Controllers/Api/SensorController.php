<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use App\Models\Alert;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    private float $minTemp = 2;
    private float $maxTemp = 8;

    private float $minHumidity = 40;
    private float $maxHumidity = 65;

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|max:100',

            // ✅ measured_at optional
            'measured_at' => 'nullable|integer',

            'temperature_c' => 'required|numeric',
            'humidity_pct' => 'required|numeric',
        ]);

        // ✅ if measured_at not sent => use laptop/server time
        $measuredAt = $request->filled('measured_at')
            ? \Carbon\Carbon::createFromTimestamp($request->measured_at)
            : now();

        $reading = SensorReading::create([
            'device_id' => $request->device_id,
            'measured_at' => $measuredAt,
            'temperature_c' => $request->temperature_c,
            'humidity_pct' => $request->humidity_pct,
        ]);

        $alerts = [];

        // Temperature check
        if ($reading->temperature_c < $this->minTemp || $reading->temperature_c > $this->maxTemp) {
            Alert::create([
                'product_id' => null,
                'type' => 'TEMP_ALERT',
                'message' => 'Temperature out of range: ' . $reading->temperature_c . '°C',
                'is_resolved' => false,
            ]);
            $alerts[] = 'temperature';
        }

        // Humidity check
        if ($reading->humidity_pct < $this->minHumidity || $reading->humidity_pct > $this->maxHumidity) {
            Alert::create([
                'product_id' => null,
                'type' => 'HUMIDITY_ALERT',
                'message' => 'Humidity out of range: ' . $reading->humidity_pct . '%',
                'is_resolved' => false,
            ]);
            $alerts[] = 'humidity';
        }

        return response()->json([
            'ok' => true,
            'reading_id' => $reading->id,
            'measured_at' => $reading->measured_at->toDateTimeString(),
            'alerts_triggered' => $alerts,
        ]);
    }
}
