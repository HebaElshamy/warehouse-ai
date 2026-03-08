<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Categories by name
        $catId = Category::pluck('id', 'name')->toArray();

        // The 10 suppliers we seeded قبل كده (لازم نفس الأسماء)
        $supplierNames = ['Ozu','Vipe','Quatz','Bluejam','InnoZ','Zoombeat','Skidoo','Tagfeed','Realpoint','Voomm'];
        $supplierIds = Supplier::whereIn('name', $supplierNames)->pluck('id')->values()->all();

        // Products grouped by category (زي اللي رتبناه)
        $data = [
            'Bakery' => [
                'All-Purpose Flour','Almond Flour','Bread Flour','Butter Biscuit','Chocolate Biscuit',
                'Digestive Biscuit','Multigrain Bread','Oatmeal Biscuit','Rye Bread','Sourdough Bread',
                'Vanilla Biscuit','White Bread','Whole Wheat Bread','Whole Wheat Flour','Rice Flour',
            ],
            'Beverages' => [
                'Arabica Coffee','Black Coffee','Black Tea','Green Coffee','Green Tea','Herbal Tea',
                'Robusta Coffee','White Tea',
            ],
            'Dairy' => [
                'Butter','Buttermilk','Cheddar Cheese','Cheese','Cottage Cheese','Cream','Evaporated Milk',
                'Feta Cheese','Gouda Cheese','Greek Yogurt','Heavy Cream','Milk','Mozzarella Cheese',
                'Parmesan Cheese','Ricotta Cheese','Sour Cream','Swiss Cheese','Whipped Cream','Yogurt',
            ],
            'Fruits & Vegetables' => [
                'Apple','Apricot','Asparagus','Banana','Bell Pepper','Blueberries','Broccoli','Cabbage',
                'Carrot','Cauliflower','Cherry','Coconut','Cucumber','Eggplant','Garlic','Grapes',
                'Green Beans','Kale','Kiwi','Lemon','Lettuce','Lime','Mango','Mushrooms','Onion','Orange',
                'Papaya','Peach','Pear','Peas','Pineapple','Plum','Pomegranate','Potato','Spinach',
                'Strawberries','Sweet Potato','Tomato','Watermelon','Zucchini',
            ],
            'Grains & Pulses' => [
                'Arborio Rice','Basmati Rice','Black Rice','Brown Rice','Jasmine Rice','Long Grain Rice',
                'Short Grain Rice','Sushi Rice','White Rice','Wild Rice',
                'Powdered Sugar','Raw Sugar','White Sugar','Coconut Sugar',
            ],
            'Oils & Fats' => [
                'Avocado Oil','Canola Oil','Coconut Oil','Corn Oil','Olive Oil','Palm Oil',
                'Peanut Oil','Sesame Oil','Sunflower Oil','Vegetable Oil',
            ],
            'Seafood' => [
                'Anchovies','Cod','Haddock','Halibut','Mackerel','Salmon','Sardines','Tilapia','Trout','Tuna',
            ],
        ];

        // Price ranges per category (منطقية)
        $priceRanges = [
            'Bakery' => [3.00, 10.00],
            'Beverages' => [4.00, 18.00],
            'Dairy' => [2.50, 15.00],
            'Fruits & Vegetables' => [1.00, 12.00],
            'Grains & Pulses' => [2.00, 14.00],
            'Oils & Fats' => [6.00, 25.00],
            'Seafood' => [8.00, 40.00],
        ];

        $prefix = 100;          // 3 digits
        $supplierIndex = 0;     // round-robin on 10 suppliers
        $locIndex = 0;          // A0, A1, A2...

        foreach ($data as $categoryName => $products) {
            $category_id = $catId[$categoryName] ?? null;

            foreach ($products as $name) {
                if ($prefix > 999) {
                    throw new \RuntimeException('No more 3-digit barcode_prefix values left (100-999).');
                }

                // Supplier (round-robin)
                $supplier_id = $supplierIds[$supplierIndex % max(count($supplierIds), 1)] ?? null;
                $supplierIndex++;

                // Warehouse location starts from A0
                $warehouse_location = 'A' . $locIndex;
                $locIndex++;

                // Stock & reorder logic
                $reorder_level = random_int(20, 120);
                $reorder_quantity = random_int(10, 100);

                // Price by category
                [$minP, $maxP] = $priceRanges[$categoryName] ?? [2.00, 20.00];
                $unit_price = round($minP + (mt_rand() / mt_getrandmax()) * ($maxP - $minP), 2);

                // Dates logic
                $date_received = Carbon::today()->subDays(random_int(30, 700)); // last ~2 years
                $last_order_date = (clone $date_received)->addDays(random_int(0, 180));
                $expiration_date = (clone $last_order_date)->addDays(random_int(30, 365));

                // Analytics fields
                $sales_volume = random_int(0, 250);

                // Inventory turnover (higher sales => higher turnover)
                $inventory_turnover_rate = round(min(300, max(1, $sales_volume + random_int(-20, 60))), 2);

                // Status logic
                // - Backordered لو الطلب غالبًا محتاج reorder (simulate low stock)
                // - Discontinued نسبة صغيرة
                $roll = random_int(1, 100);
                if ($roll <= 8) {
                    $status = 'Discontinued';
                } elseif ($roll <= 25) {
                    $status = 'Backordered';
                } else {
                    $status = 'Active';
                }

                Product::updateOrCreate(
                    ['barcode_prefix' => (string)$prefix],
                    [
                        'category_id' => $category_id,
                        'supplier_id' => $supplier_id,

                        'product_name' => $name,
                        'unit' => 'Unit',

                        'unit_price' => $unit_price,

                        'date_received' => $date_received->toDateString(),
                        'last_order_date' => $last_order_date->toDateString(),
                        'expiration_date' => $expiration_date->toDateString(),

                        'warehouse_location' => $warehouse_location,

                        'reorder_level' => $reorder_level,
                        'reorder_quantity' => $reorder_quantity,

                        'sales_volume' => $sales_volume,
                        'inventory_turnover_rate' => $inventory_turnover_rate,

                        'status' => $status,
                        'is_active' => true, // زي ما اتفقنا مبدئيًا كله active
                    ]
                );

                $prefix++;
            }
        }
    }
}
