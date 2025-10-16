<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductPricesSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener tipos de precios (clave = slug)
        $priceTypes = DB::table('price_types')->pluck('id', 'slug');

        // Buscar todas las unidades creadas
        $units = DB::table('product_units')->get();

        foreach ($units as $unit) {
            // Buscar el producto asociado a la unidad
            $product = DB::table('products')->where('id', $unit->product_id)->first();

            // Precio base en función del costo y conversión
            $baseCost = $product->cost * $unit->conversion_factor;

            // Insertar precios por cada tipo
            DB::table('product_prices')->insert([
                [
                    'product_unit_id' => $unit->id,
                    'price_type_id'   => $priceTypes['contado'],
                    'price_usd'       => $baseCost * 1.30,
                    'profit_percentage' => 30,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'product_unit_id' => $unit->id,
                    'price_type_id'   => $priceTypes['mayor'],
                    'price_usd'       => $baseCost * 1.15,
                    'profit_percentage' => 15,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'product_unit_id' => $unit->id,
                    'price_type_id'   => $priceTypes['credito'],
                    'price_usd'       => $baseCost * 1.50,
                    'profit_percentage' => 50,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ]);
        }
    }
}
