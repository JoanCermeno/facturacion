<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Companies;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Crear un cliente por defecto por cada empresa
        $companies = Companies::all();

        foreach ($companies as $company) {
            Customer::firstOrCreate([
                'companies_id' => $company->id,
                'name' => 'Cliente casual',
            ], [
                'id_card' => null,
                'phone' => null,
                'email' => null,
                'address' => null,
            ]);
        }
    }
}
