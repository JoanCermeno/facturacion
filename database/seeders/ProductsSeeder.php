<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        // =======================================================
        // Toma el ID de la moneda DOLAR AMERICANO COMO BASE.
        // =======================================================
        $currencyId = 1;

        // =============================
        // Producto 1: Sardinas en lata
        // =============================
        $sardinaId = DB::table('products')->insertGetId([
            'companies_id' => 1,
            'department_id' => DB::table('departments')->insertGetId([
                'companies_id' => 1,
                'code' => 'DEP-001',
                'description' => 'Viveres',
                'type' => 'unit',
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            'code' => 'PRD-001',
            'name' => 'Sardinas en Lata',
            'description' => 'Lata de sardinas en aceite vegetal',
            'cost' => 0.80,
            'base_unit' => 'unit',
            'currency_id' => $currencyId, // 💱 nueva relación
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_units')->insert([
            ['product_id' => $sardinaId, 'unit_type' => 'unit', 'conversion_factor' => 1,  'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $sardinaId, 'unit_type' => 'pack', 'conversion_factor' => 6,  'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $sardinaId, 'unit_type' => 'box',  'conversion_factor' => 12, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =============================
        // Producto 2: Aceite 20W50
        // =============================
        $aceiteId = DB::table('products')->insertGetId([
            'companies_id' => 1,
            'department_id' => 1,
            'code' => 'PRD-002',
            'name' => 'Aceite 20W50',
            'description' => 'Aceite de motor 20W50 galón',
            'cost' => 8.00,
            'base_unit' => 'lt',
            'currency_id' => $currencyId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_units')->insert([
            ['product_id' => $aceiteId, 'unit_type' => 'lt',  'conversion_factor' => 1,     'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $aceiteId, 'unit_type' => 'gal', 'conversion_factor' => 3.785, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =============================
        // Producto 3: Arroz
        // =============================
        $arrozId = DB::table('products')->insertGetId([
            'companies_id' => 1,
            'department_id' => 1,
            'code' => 'PRD-003',
            'name' => 'Arroz Blanco',
            'description' => 'Arroz de grano largo',
            'cost' => 0.50,
            'base_unit' => 'kg',
            'currency_id' => $currencyId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_units')->insert([
            ['product_id' => $arrozId, 'unit_type' => 'kg',  'conversion_factor' => 1,   'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $arrozId, 'unit_type' => 'lb',  'conversion_factor' => 2.2, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $arrozId, 'unit_type' => 'box', 'conversion_factor' => 20,  'created_at' => now(), 'updated_at' => now()],
        ]);

        // =============================
        // Producto 4: Agua mineral
        // =============================
        $aguaId = DB::table('products')->insertGetId([
            'companies_id' => 1,
            'department_id' => 1,
            'code' => 'PRD-004',
            'name' => 'Agua Mineral',
            'description' => 'Botella de agua mineral 500ml',
            'cost' => 0.30,
            'base_unit' => 'unit',
            'currency_id' => $currencyId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_units')->insert([
            ['product_id' => $aguaId, 'unit_type' => 'box', 'conversion_factor' => 12, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
