<?php

use App\Models\Companies;
use App\Models\Department;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Currency;
use App\Models\PriceType; // 👈 Importante agregar el modelo PriceType

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuramos el entorno base para todos los tests
    $this->company = Companies::factory()->create();
    $this->currency = Currency::factory()->create();

    // 🚀 LA CORRECCIÓN: Creamos los Tipos de Precio para la empresa
    $this->priceTypeContado = PriceType::create(['companies_id' => $this->company->id, 'name' => 'Contado', 'slug' => 'contado']);
    $this->priceTypeMayor = PriceType::create(['companies_id' => $this->company->id, 'name' => 'Mayor', 'slug' => 'mayor']);
    $this->priceTypeCredito = PriceType::create(['companies_id' => $this->company->id, 'name' => 'Crédito', 'slug' => 'credito']);

    $this->admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $this->company->id,
    ]);

    $this->department = Department::factory()->create([
        'companies_id' => $this->company->id,
    ]);
});

test('un admin puede ver todos los productos que le pertenecen a su empresa', function () {
    $product = Product::factory()->create([
        'companies_id' => $this->company->id,
        'department_id' => $this->department->id,
        'currency_id' => $this->currency->id, // Aseguramos que tenga currency_id si lo requiere
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/products');

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
                        'cost',
                        'stock',
                        'is_decimal',
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

    $this->assertEquals($product->id, $response['products']['data'][0]['id']);
});

test('un admin puede crear un producto', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/products', [
        'code' => '123456789',
        'name' => 'Producto 1',
        'description' => 'Descripción del producto 1',
        'cost' => 100,
        'is_decimal' => false,
        'base_unit' => 'unit',
        'companies_id' => $this->company->id,
        'department_id' => $this->department->id,
        'currency_id' => $this->currency->id
    ]);

    // 🚀 LA CORRECCIÓN 2: Ajustamos el mensaje de éxito esperado
    $response->assertStatus(201)->assertJson([
        'message' => 'Producto registrado con unidades y precios base ✅',
        'product' => [
            'code' => '123456789',
            'name' => 'Producto 1',
            'description' => 'Descripción del producto 1',
            'cost' => 100,
            'base_unit' => 'unit', // 👈 ESTE ERA EL DETALLE (minúscula)
            'is_decimal' => false,
            'companies_id' => $this->company->id,
            'department_id' => $this->department->id,
        ],
    ]);

    // Opcional: Verificar que se crearon los precios base
    $productId = $response->json('product.id');
    $this->assertDatabaseHas('product_units', [
        'product_id' => $productId,
        'unit_type' => 'Unit', // 👈 Aquí también en minúscula
    ]);
});

test('un admin puede actualizar un producto', function () {
    $product = Product::factory()->create([
        'companies_id' => $this->company->id,
        'department_id' => $this->department->id,
        'currency_id' => $this->currency->id,
        'cost' => 100,
        'base_unit' => 'Unit'
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/products/{$product->id}", [
        'name' => 'Producto Actualizado',
        'description' => 'Nueva descripción del producto',
        'cost' => 150,
        'base_unit' => 'Box', // Ojo si esto debe cambiar
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Producto actualizado correctamente ✅',
            'product' => [
                'id' => $product->id,
                'name' => 'Producto Actualizado',
                'description' => 'Nueva descripción del producto',
                'cost' => 150,
                'base_unit' => 'Box', // Asumiendo que se formatea
                'companies_id' => $this->company->id,
                'department_id' => $this->department->id,
            ],
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Producto Actualizado',
        'description' => 'Nueva descripción del producto',
        'cost' => 150,
    ]);
});

test('un admin puede eliminar un producto', function () {
    $product = Product::factory()->create([
        'companies_id' => $this->company->id,
        'department_id' => $this->department->id,
        'currency_id' => $this->currency->id,
    ]);

    $response = $this->actingAs($this->admin)->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Producto eliminado correctamente 🗑️',
        ]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});