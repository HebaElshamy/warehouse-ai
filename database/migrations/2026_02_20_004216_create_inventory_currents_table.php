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
    Schema::create('inventory_current', function (Blueprint $table) {
        $table->foreignId('product_id')
            ->primary()
            ->constrained('products')
            ->cascadeOnDelete();

        $table->unsignedInteger('current_stock')->default(0);
        $table->enum('status', ['OK', 'LOW'])->default('OK');

        $table->enum('last_updated_source', ['MANUAL', 'EXCEL', 'SCAN'])
            ->default('MANUAL');

        $table->timestamp('last_seen_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('inventory_current');
}
};
