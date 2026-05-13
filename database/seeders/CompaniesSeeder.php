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
                "name" => "Empresa De ejemlo",
                "address" => "calle cualquiera",
                "phone" => "04145057588",
                "email" => "myempresa@gmail.com",
                "invoice_sequence" => 1,
                'auto_code_products' => true,
                'auto_code_departments' => true,
                'product_code_prefix' => 'P',
                'department_code_prefix' => 'D',
                'logo_path' => null,
            ]
        );
    }
}
