<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Product;
use App\Models\InventoryOperation;
use App\Models\InventoryOperationDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un usuario puede crear una operación de inventario con items', function () {
    // Crear compañía
    $company = Companies::factory()->create();

    // Crear usuario autenticado
    $user = User::factory()->create([
        'companies_id' => $company->id,
    ]);

    // Crear productos asociados a la compañía
    $product1 = Product::factory()->create([
        'companies_id' => $company->id,
        'stock' => 10,
    ]);

    $product2 = Product::factory()->create([
        'companies_id' => $company->id,
        'stock' => 5,
    ]);

    // Datos de la operación
    $payload = [
        'operation_type' => 'cargo',
        'note' => 'Ingreso de productos nuevos',
        'responsible' => 'Joan',
        'operation_date' => now()->toDateString(),
        'items' => [
            ['product_id' => $product1->id, 'quantity' => 20],
            ['product_id' => $product2->id, 'quantity' => 5],
        ],
    ];

    // Petición autenticada
    $response = $this->actingAs($user)->postJson('/api/inventory-operations', $payload);

    // Verificar respuesta
    $response->assertStatus(201)
             ->assertJson([
                 'message' => 'Operación creada correctamente ✅',
                 'operation' => [
                     'operation_type' => 'cargo',
                     'note' => 'Ingreso de productos nuevos',
                 ],
             ]);

    // Verificar base de datos
    $this->assertDatabaseHas('inventory_operations', [
        'operation_type' => 'cargo',
        'note' => 'Ingreso de productos nuevos',
        'responsible' => 'Joan',
    ]);

    $this->assertDatabaseHas('inventory_operation_details', [
        'product_id' => $product1->id,
        'quantity' => 20,
    ]);

    $this->assertDatabaseHas('inventory_operation_details', [
        'product_id' => $product2->id,
        'quantity' => 5,
    ]);
});

test('una operación inválida retorna error de validación', function () {
    $user = User::factory()->create();

    $payload = [
        'operation_type' => 'invalido',
        'note' => '',
        'responsible' => '',
        'items' => [],
    ];

    $response = $this->actingAs($user)->postJson('/api/inventory-operations', $payload);

    $response->assertStatus(422);
});
