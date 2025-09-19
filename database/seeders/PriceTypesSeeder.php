<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('price_types')->insert([
            [
                'companies_id' => 1, // ⚡ Por ahora, prueba con la empresa ID 1
                'name' => 'Precio al Contado',
                'slug' => 'contado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'companies_id' => 1,
                'name' => 'Precio al Mayor',
                'slug' => 'mayor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'companies_id' => 1,
                'name' => 'Precio a Crédito',
                'slug' => 'credito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
