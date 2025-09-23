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
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
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
    }
}
