<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\PriceTypesSeeder;
//seeder para la empresa
use Database\Seeders\CompaniesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        $this->call(CurrencySeeder::class);
        $this->call(CompaniesSeeder::class);
        $this->call(PriceTypesSeeder::class);
    }
}
