<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $days = 30; // آخر 30 يوم مبيعات
        $end = Carbon::today();
        $start = (clone $end)->subDays($days - 1);

        // هات كل المنتجات
        $products = DB::table('products')
            ->select('id', 'reorder_level', 'reorder_quantity')
            ->orderBy('id')
            ->get();

        foreach ($products as $p) {

            // 1) inventory_current: خلي stock منطقية بدل 0
            // خليها حوالين reorder_level (أعلى شوية أو أقل شوية)
            $baseStock = max(0, (int)$p->reorder_level + random_int(-10, 60));

            DB::table('inventory_current')->updateOrInsert(
                ['product_id' => $p->id],
                [
                    'current_stock' => $baseStock,
                    'status' => ($baseStock <= (int)$p->reorder_level) ? 'LOW' : 'OK',
                    'last_updated_source' => 'EXCEL',
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                    // لو الريكورد جديد
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );

            // 2) sales_dailies: مبيعات يومية لآخر 30 يوم
            $monthlySum = 0;

            $cursor = $start->copy();
            while ($cursor <= $end) {

                // مبيعات منطقية: حوالين 0 .. 25
                // خليها أعلى شوية لو reorder_level كبير (تجريبي)
                $base = max(0, (int)round(((int)$p->reorder_level) / 15));
                $sales = max(0, random_int(0, 8) + $base);

                // نزود شوية variability
                if (random_int(1, 100) <= 15) {
                    $sales += random_int(3, 12);
                }

                $monthlySum += $sales;

                DB::table('sales_dailies')->updateOrInsert(
                    [
                        'product_id' => $p->id,
                        'sale_date' => $cursor->toDateString(),
                    ],
                    [
                        'sales_volume' => $sales,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );

                $cursor->addDay();
            }

            // 3) sales_references: مجموع آخر 30 يوم (كـ monthly reference)
            DB::table('sales_references')->updateOrInsert(
                ['product_id' => $p->id],
                [
                    'sales_volume_monthly' => (float)$monthlySum,
                    'source' => 'EXCEL',
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
