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
    Schema::create('products', function (Blueprint $table) {
        $table->id();

        $table->foreignId('category_id')
            ->nullable()
            ->constrained('categories')
            ->nullOnDelete();

        $table->string('barcode_prefix', 3)->unique();; // e.g. 222
        $table->string('product_name');
        $table->string('unit')->nullable(); // e.g., Carton

        $table->unsignedInteger('reorder_level')->default(0);
        $table->unsignedInteger('reorder_quantity')->default(0);

        $table->boolean('is_active')->default(true);
        $table->timestamps();


    });
}

public function down(): void
{
    Schema::dropIfExists('products');
}
};
