<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Currency;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede listar los métodos de pago de su empresa', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
        'name' => 'Dólar',
        'symbol' => 'USD',
        'exchange_rate' => 1,
        'is_base' => true,
    ]);

    PaymentMethod::factory()->count(3)->create([
        'companies_id' => $company->id,
        'currency_id' => $currency->id,
    ]);

    $response = $this->actingAs($admin)->getJson('/api/payment-methods');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'id',
                'companies_id',
                'code',
                'description',
                'currency_id',
                'status',
                'created_at',
                'updated_at'
            ]
        ]);
});

test('un admin puede crear un metodo de pago', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    // Usar el nombre "Bolívar Venezolano" para que coincida con lo que esperas en el fragmento y la respuesta de tu API
    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
        'name' => 'Bolívar Venezolano', // Corregido aquí
        'symbol' => 'VES',
        'exchange_rate' => 36.5,
        'is_base' => false,
    ]);

    $data = [
        'code' => 'PAGOMOVIL',
        'description' => 'Pago Móvil Banco Venezuela',
        'currency_id' => $currency->id,
        'status' => 'activo',
    ];

    $response = $this->actingAs($admin)->postJson('/api/payment-methods', $data);

    $response->assertStatus(201)
        // Usamos assertJson en lugar de assertJsonFragment para la estructura completa del objeto
        // Puedes usar assertJsonFragment si solo quieres verificar un subconjunto,
        // pero eliminaremos las claves de tiempo para la prueba de fragmento:
        ->assertJsonFragment([
            'code' => 'PAGOMOVIL',
            'description' => 'Pago Móvil Banco Venezuela',
            'currency_id' => $currency->id,
            'status' => 'activo',
            'companies_id' => $company->id,
            // Las claves 'id', 'created_at', 'updated_at' son dinámicas, no se prueban en un fragmento simple
            // La clave 'currency' es un objeto anidado, lo verificamos por separado o en la estructura.
        ])
        ->assertJson([ // Usamos assertJson para verificar la estructura con la relación cargada.
            'currency' => [
                'id' => $currency->id,
                'companies_id' => $company->id,
                'symbol' => 'VES',
                'name' => 'Bolívar Venezolano', // Este es el valor que causaba el fallo de coincidencia
                'exchange_rate' => 36.5,
                'is_base' => false,
                // Omitir 'created_at' y 'updated_at' ya que son dinámicos
            ]
        ], true); // El 'true' asegura que no haya claves extra en el fragmento 'currency' que no estén especificadas.

    $this->assertDatabaseHas('payment_methods', [
        'code' => 'PAGOMOVIL',
        'companies_id' => $company->id,
        'currency_id' => $currency->id, // Asegurarse de que el currency_id es correcto
    ]);
});

test('un admin puede actualizar un método de pago', function () {
    $company = Companies::factory()->create();
    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
    ]);

    $method = PaymentMethod::factory()->create([
        'companies_id' => $company->id,
        'currency_id' => $currency->id,
        'code' => 'EFECTIVOUSD',
        'status' => 'activo',
    ]);

    $response = $this->actingAs($admin)->putJson("/api/payment-methods/{$method->id}", [
        'description' => 'Efectivo en dólares americanos',
        'status' => 'inactivo',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([ // Verifica que los datos modificados se reflejen en la respuesta
            'description' => 'Efectivo en dólares americanos',
            'status' => 'inactivo', // El estado verificado
        ]);

    $this->assertDatabaseHas('payment_methods', [
        'id' => $method->id,
        'status' => 'inactivo',
    ]);
});

test('un admin puede eliminar un método de pago', function () {
    $company = Companies::factory()->create();
    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
    ]);

    $method = PaymentMethod::factory()->create([
        'companies_id' => $company->id,
        'currency_id' => $currency->id,
        'code' => 'ZELLE',
    ]);

    $response = $this->actingAs($admin)->deleteJson("/api/payment-methods/{$method->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Método de pago eliminado correctamente.',
        ]);

    $this->assertDatabaseMissing('payment_methods', [
        'id' => $method->id,
    ]);
});
