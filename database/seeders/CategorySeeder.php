<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Bakery', 'description' => 'Bakery products', 'is_active' => true],
            ['name' => 'Beverages', 'description' => 'Drinks and beverages', 'is_active' => true],
            ['name' => 'Dairy', 'description' => 'Milk and dairy products', 'is_active' => true],
            ['name' => 'Fruits & Vegetables', 'description' => 'Fresh fruits and vegetables', 'is_active' => true],
            ['name' => 'Grains & Pulses', 'description' => 'Rice, flour, grains and pulses', 'is_active' => true],
            ['name' => 'Oils & Fats', 'description' => 'Cooking oils and fats', 'is_active' => true],
            ['name' => 'Seafood', 'description' => 'Fish and seafood products', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate($category);
        }
    }
}
