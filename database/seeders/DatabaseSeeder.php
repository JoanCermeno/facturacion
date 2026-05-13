<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\PriceTypesSeeder;
//seeder para la empresa
use Database\Seeders\CompaniesSeeder;
use Database\Seeders\ProductsSeeder;
//seeder para los métodos de pago
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\CustomerSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // companias seeder
        $this->call(CompaniesSeeder::class);

        User::factory()->create([
            'name' => 'Usuario de ejemplo',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'companies_id' => 1,
        ]);

        // Monedas de la empresa seeder
        $this->call(CurrencySeeder::class);
        // tipos de precios seeder
        $this->call(PriceTypesSeeder::class);
        // productos seeder
        $this->call(ProductsSeeder::class);
        // precios de productos seeder
        $this->call(ProductPricesSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(CustomerSeeder::class);

    }
}
