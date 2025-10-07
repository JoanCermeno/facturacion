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

    $department = Department::factory()->create([
        'companies_id' => $company->id,
    ]);

    $product = Product::factory()->create([
        'companies_id' => $company->id,
        'department_id' => $department->id,
    ]);

    $response = $this->actingAs($admin)->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Productos obtenidos correctamente ✅',
        ])
        ->assertJsonStructure([
            'message',
            'products' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'companies_id',
                        'department_id',
                        'code',
                        'name',
                        'description',
                        'cost_usd',
                        'stock',
                        'base_unit',
                        'created_at',
                        'updated_at',
                        'department'
                    ]
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]
        ]);

    // y opcionalmente verificar que tu producto sí está en la página
    $this->assertEquals($product->id, $response['products']['data'][0]['id']);
});


test('un admin puede crear un producto', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $departament_id = Department::factory()->create([
        'companies_id' => $company->id,
    ])->id;
    
    $response = $this->actingAs($admin)->postJson('/api/products', [
        'code' => '123456789',
        'name' => 'Producto 1',
        'description' => 'Descripción del producto 1',
        'cost_usd' => 100,
        'base_unit' => 'unit',
        'companies_id' => $company->id,
        'department_id' => $departament_id]);

    $response->assertStatus(201)->assertJson([
        'message' => 'Producto registrado correctamente ✅',
        'product' => [
            'code' => '123456789',
            'name' => 'Producto 1',
            'description' => 'Descripción del producto 1',
            'cost_usd' => 100,
            'base_unit' => 'unit',
            'companies_id' => $company->id,
            'department_id' => $departament_id,
        ],
    ]);
});



test('un admin puede actualizar un producto', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $department = Department::factory()->create([
        'companies_id' => $company->id,
    ]);

    $product = Product::factory()->create([
        'companies_id' => $company->id,
        'department_id' => $department->id,
    ]);

    $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", [
        'name' => 'Producto Actualizado',
        'description' => 'Nueva descripción del producto',
        'cost_usd' => 150,
        'base_unit' => 'box',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Producto actualizado correctamente ✅',
            'product' => [
                'id' => $product->id,
                'name' => 'Producto Actualizado',
                'description' => 'Nueva descripción del producto',
                'cost_usd' => 150,
                'base_unit' => 'box',
                'companies_id' => $company->id,
                'department_id' => $department->id,
            ],
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Producto Actualizado',
        'description' => 'Nueva descripción del producto',
        'cost_usd' => 150,
        'base_unit' => 'box',
    ]);
});


test('un admin puede eliminar un producto', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $department = Department::factory()->create([
        'companies_id' => $company->id,
    ]);

    $product = Product::factory()->create([
        'companies_id' => $company->id,
        'department_id' => $department->id,
    ]);

    $response = $this->actingAs($admin)->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Producto eliminado correctamente 🗑️',
        ]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

