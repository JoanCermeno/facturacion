<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =============================
        // Producto 1: Sardinas en lata
        // =============================
        $sardinaId = DB::table('products')->insertGetId([
            'companies_id' => 1,
            'department_id' => null,
            'code' => 'PRD-001',
            'name' => 'Sardinas en Lata',
            'description' => 'Lata de sardinas en aceite vegetal',
            'cost_usd' => 0.80,
            'base_unit' => 'unit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_units')->insert([
            ['product_id' => $sardinaId, 'unit_type' => 'unit', 'conversion_factor' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $sardinaId, 'unit_type' => 'pack', 'conversion_factor' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $sardinaId, 'unit_type' => 'box',  'conversion_factor' => 12,'created_at' => now(), 'updated_at' => now()],
        ]);

        // =============================
        // Producto 2: Aceite 20W50
        // =============================
        $aceiteId = DB::table('products')->insertGetId([
            'companies_id' => 1,
            'department_id' => null,
            'code' => 'PRD-002',
            'name' => 'Aceite 20W50',
            'description' => 'Aceite de motor 20W50 galón',
            'cost_usd' => 8.00,
            'base_unit' => 'lt',
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
            'department_id' => null,
            'code' => 'PRD-003',
            'name' => 'Arroz Blanco',
            'description' => 'Arroz de grano largo',
            'cost_usd' => 0.50,
            'base_unit' => 'kg',
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
            'department_id' => null,
            'code' => 'PRD-004',
            'name' => 'Agua Mineral',
            'description' => 'Botella de agua mineral 500ml',
            'cost_usd' => 0.30,
            'base_unit' => 'unit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_units')->insert([
            ['product_id' => $aguaId, 'unit_type' => 'box', 'conversion_factor' => 12,  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
