<?php


use App\Models\Companies;
use App\Models\Department;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede ver todos los productos que le pertenecen a su empresa', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    //cargamos los productos a la empresa
    $product = Product::factory()->create([
        'companies_id' => $company->id,
        'department_id' => Department::factory()->create([
            'companies_id' => $company->id,
        ]),
    ]);

    $response = $this->actingAs($admin)->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Productos obtenidos correctamente ✅',
            'products' => [
                [
                    'id' => $product->id,
                    'code' => $product->code,
                    'description' => $product->description,
                    'cost_usd' => $product->cost_usd,
                    'base_unit' => $product->base_unit,
                    'companies_id' => $product->companies_id,
                    'department_id' => $product->department_id,
                ],
            ],
        ]);

});

