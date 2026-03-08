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
    Schema::create('scan_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('scan_event_id')
            ->constrained('scan_events')
            ->cascadeOnDelete();

        $table->string('barcode');

        $table->foreignId('product_id')
            ->nullable()
            ->constrained('products')
            ->nullOnDelete();

        $table->boolean('is_unknown')->default(false);
        $table->timestamps();

        $table->index('barcode');
    });
}

public function down(): void
{
    Schema::dropIfExists('scan_items');
}
};
