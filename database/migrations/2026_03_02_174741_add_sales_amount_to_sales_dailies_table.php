<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_dailies', function (Blueprint $table) {
            $table->decimal('sales_amount', 12, 2)->default(0)->after('sales_volume');
        });
    }

    public function down(): void
    {
        Schema::table('sales_dailies', function (Blueprint $table) {
            $table->dropColumn('sales_amount');
        });
    }
};
