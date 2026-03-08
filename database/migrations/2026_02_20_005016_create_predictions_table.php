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
   Schema::create('predictions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('product_id')
        ->constrained('products')
        ->cascadeOnDelete();

    $table->unsignedInteger('horizon_days')->default(7);
    $table->decimal('predicted_demand', 10, 2)->default(0);
    $table->unsignedInteger('suggested_order_qty')->default(0);

    $table->string('model_version')->default('baseline_v1');
    $table->timestamp('generated_at')->useCurrent();

    $table->timestamps();

    $table->index(['product_id', 'generated_at']);
});
}

public function down(): void
{
    Schema::dropIfExists('predictions');
}
};
