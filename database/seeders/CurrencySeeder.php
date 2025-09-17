<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('currencies')->insert([
            [
                'symbol' => 'USD',
                'name' => 'Dólar Americano',
                'exchange_rate' => 1.00,
                'is_base' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'VES',
                'name' => 'Bolívar Venezolano',
                'exchange_rate' => 36.50,
                'is_base' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'COP',
                'name' => 'Peso Colombiano',
                'exchange_rate' => 4200.00,
                'is_base' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
