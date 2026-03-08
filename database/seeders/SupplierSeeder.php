<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Ozu', 'phone' => '01000000001', 'email' => 'contact@ozu.com', 'address' => 'Cairo, Egypt', 'is_active' => true],
            ['name' => 'Vipe', 'phone' => '01000000002', 'email' => 'info@vipe.com', 'address' => 'Giza, Egypt', 'is_active' => true],
            ['name' => 'Quatz', 'phone' => '01000000003', 'email' => 'sales@quatz.com', 'address' => 'Alexandria, Egypt', 'is_active' => true],
            ['name' => 'Bluejam', 'phone' => '01000000004', 'email' => 'support@bluejam.com', 'address' => 'Mansoura, Egypt', 'is_active' => true],
            ['name' => 'InnoZ', 'phone' => '01000000005', 'email' => 'info@innoz.com', 'address' => 'Tanta, Egypt', 'is_active' => true],
            ['name' => 'Zoombeat', 'phone' => '01000000006', 'email' => 'contact@zoombeat.com', 'address' => 'Zagazig, Egypt', 'is_active' => true],
            ['name' => 'Skidoo', 'phone' => '01000000007', 'email' => 'sales@skidoo.com', 'address' => 'Ismailia, Egypt', 'is_active' => true],
            ['name' => 'Tagfeed', 'phone' => '01000000008', 'email' => 'info@tagfeed.com', 'address' => 'Port Said, Egypt', 'is_active' => true],
            ['name' => 'Realpoint', 'phone' => '01000000009', 'email' => 'contact@realpoint.com', 'address' => 'Asyut, Egypt', 'is_active' => true],
            ['name' => 'Voomm', 'phone' => '01000000010', 'email' => 'support@voomm.com', 'address' => 'Sohag, Egypt', 'is_active' => true],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate($supplier);
        }
    }
}
