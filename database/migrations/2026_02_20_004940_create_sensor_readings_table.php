<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::create('sensor_readings', function (Blueprint $table) {
        $table->id();
        $table->string('device_id');
        $table->timestamp('measured_at');
        $table->float('temperature_c');
        $table->float('humidity_pct');
        $table->timestamps();

        $table->index(['device_id', 'measured_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('sensor_readings');
}
};
