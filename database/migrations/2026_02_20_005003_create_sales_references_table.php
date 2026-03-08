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
   Schema::create('sales_references', function (Blueprint $table) {
    $table->id();

    $table->foreignId('product_id')
        ->constrained('products')
        ->cascadeOnDelete();

    $table->unsignedInteger('sales_volume_monthly')->default(0);
    $table->string('source')->default('EXCEL');

    $table->timestamps();

    $table->unique('product_id');
});
}

public function down(): void
{
    Schema::dropIfExists('sales_references');
}
};
