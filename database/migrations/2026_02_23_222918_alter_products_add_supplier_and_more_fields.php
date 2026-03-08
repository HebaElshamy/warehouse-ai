<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // Supplier (each product has one supplier)
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('category_id')
                ->constrained('suppliers')
                ->nullOnDelete();

            // Price
            $table->decimal('unit_price', 10, 2)
                ->nullable()
                ->after('unit');

            // Dates (optional)
            $table->date('date_received')->nullable()->after('unit_price');
            $table->date('last_order_date')->nullable()->after('date_received');
            $table->date('expiration_date')->nullable()->after('last_order_date');

            // Location
            $table->string('warehouse_location')->nullable()->after('expiration_date');

            // Excel analytics fields (keep them for now)
            $table->unsignedInteger('sales_volume')->nullable()->after('warehouse_location');
            $table->decimal('inventory_turnover_rate', 10, 2)->nullable()->after('sales_volume');

            // Status like Excel (separate from is_active)
            $table->enum('status', ['Active', 'Discontinued', 'Backordered'])
                ->default('Active')
                ->after('inventory_turnover_rate');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // Drop enum/status and extra columns
            $table->dropColumn([
                'supplier_id',
                'unit_price',
                'date_received',
                'last_order_date',
                'expiration_date',
                'warehouse_location',
                'sales_volume',
                'inventory_turnover_rate',
                'status',
            ]);
        });
    }
};
