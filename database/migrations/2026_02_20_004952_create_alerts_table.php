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
    Schema::create('alerts', function (Blueprint $table) {
           $table->id();

        $table->foreignId('product_id')
            ->nullable()
            ->constrained('products')
            ->cascadeOnDelete();

        $table->string('type'); // LOW_STOCK / FORECAST
        $table->string('message');

        $table->boolean('is_resolved')->default(false);
        $table->timestamp('resolved_at')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('alerts');
}
};
