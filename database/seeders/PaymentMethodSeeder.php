<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use App\Models\Companies;
class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $company = Companies::first();

        $usd = Currency::where('name', 'Dólar Americano')->first();
        $vef = Currency::where('name', 'Bolívar Venezolano')->first();
        $cop = Currency::where('name', 'Peso Colombiano')->first();

        PaymentMethod::insert([
            ['companies_id' => $company->id, 'code' => 'EFECTIVO_USD', 'description' => 'Efectivo Dólares', 'currency_id' => $usd->id, 'status' => 'activo'],
            ['companies_id' => $company->id, 'code' => 'EFECTIVO_VEF', 'description' => 'Efectivo Bolívares', 'currency_id' => $vef->id, 'status' => 'activo'],
            ['companies_id' => $company->id, 'code' => 'EFECTIVO_COP', 'description' => 'Efectivo Pesos', 'currency_id' => $cop->id, 'status' => 'activo'],
            ['companies_id' => $company->id, 'code' => 'ZELLE', 'description' => 'Pago por Zelle', 'currency_id' => $usd->id, 'status' => 'activo'],
            ['companies_id' => $company->id, 'code' => 'PAGOMOVIL', 'description' => 'Pago Móvil', 'currency_id' => $vef->id, 'status' => 'activo'],
        ]);
    }
}
