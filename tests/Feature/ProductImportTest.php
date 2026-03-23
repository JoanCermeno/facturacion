<?php

use App\Models\Companies;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product Import', function () {
    it('can import products in bulk', function () {
        // Crear empresa
        $company = Companies::factory()->create();

        // Crear moneda base para la empresa
        Currency::factory()->create([
            'companies_id' => $company->id,
            'is_base' => true,
        ]);

        // Crear usuario asociado a la empresa
        $user = User::factory()->create([
            'companies_id' => $company->id,
        ]);

        // Datos de productos para importar
        $products = [
            [
                'name' => 'Producto 1',
                'code' => 'P1',
                'cost' => 100.00,
                'base_unit' => 'unit',
                'description' => 'Descripción del producto 1',
                'reference' => 'REF1',
            ],
            [
                'name' => 'Producto 2',
                'code' => 'P2',
                'cost' => 200.00,
                'base_unit' => 'box',
                'description' => 'Descripción del producto 2',
                'reference' => 'REF2',
            ],
        ];

        // Hacer la petición POST al endpoint de importación
        $response = $this->actingAs($user)->postJson('/api/products/import', [
            'products' => $products,
        ]);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Importación completada.',
                     'created' => 2,
                     'errors' => [],
                 ]);

        // Verificar que los productos se crearon en la base de datos
        $this->assertDatabaseHas('products', [
            'name' => 'Producto 1',
            'code' => 'P1',
            'companies_id' => $company->id,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Producto 2',
            'code' => 'P2',
            'companies_id' => $company->id,
        ]);

        // Verificar que se creó el departamento "Carga Masiva"
        $this->assertDatabaseHas('departments', [
            'description' => 'Carga Masiva',
            'companies_id' => $company->id,
            'code' => 'CARGA-MASIVA-' . $company->id,
        ]);
    });
});