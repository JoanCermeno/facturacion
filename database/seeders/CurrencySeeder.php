<?php

namespace Database\Seeders;

use App\Models\Companies;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $company = Companies::first() ?? Companies::factory()->create();

        Currency::insert([
            [
                'companies_id' => $company->id,
                'name' => 'Dólar Americano',
                'symbol' => 'USD',
                'exchange_rate' => 1,
                'is_base' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'companies_id' => $company->id,
                'name' => 'Bolívar Venezolano',
                'symbol' => 'VES',
                'exchange_rate' => 36.5,
                'is_base' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'companies_id' => $company->id,
                'name' => 'Peso Colombiano',
                'symbol' => 'COP',
                'exchange_rate' => 4200,
                'is_base' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}