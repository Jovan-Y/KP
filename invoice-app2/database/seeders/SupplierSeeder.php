<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::create(['name' => 'PT. Maju Mundur', 'phone' => '081234567890', 'company_name' => 'Supplier Elektronik']);
        Supplier::create(['name' => 'CV. Jaya Abadi', 'phone' => '087654321098', 'company_name' => 'Supplier Alat Tulis']);
        Supplier::create(['name' => 'UD. Sentosa', 'phone' => '089876543210', 'company_name' => 'Supplier Bahan Bangunan']);
    }
}