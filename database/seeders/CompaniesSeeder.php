<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompaniesSeeder extends Seeder
{
    /**
     * companies table seeder
     */
    public function run(): void
    {
        //
        DB::table('companies')->insert(
            [
                "name" => "Joan C.A",
                "address" => "calle cualquiera",
                "phone" => "04145057588",
                "email" => "emporesa@email.com",
                "invoice_sequence" => 1,
                'auto_code_products' => true,
                'auto_code_departments' => true,
                'product_code_prefix' => 'PROD-',
                'department_code_prefix' => 'DEP-',
                'logo_path' => null,
            ]
        );
    }
}
