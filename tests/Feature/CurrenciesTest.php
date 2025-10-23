<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede listar las monedas de su empresa', function () {
    $Companies = Companies::factory()->create();
    $admin = User::factory()->create(['companies_id' => $Companies->id]);
    $this->actingAs($admin);

    // Reemplazado Currency::factory()->create()
    Currency::create([
        'companies_id' => $Companies->id,
        'name' => 'Dólar',
        'symbol' => 'USD',
        'exchange_rate' => 1,
        'is_base' => true,
    ]);

    $response = $this->getJson('/api/currencies');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Monedas obtenidas correctamente ✅',
        ])
        ->assertJsonStructure([
            'message',
            'currencies' => [
                '*' => ['id', 'name', 'symbol', 'exchange_rate', 'is_base', 'companies_id']
            ]
        ]);
});

test('un admin puede crear una moneda', function () {
    $Companies = Companies::factory()->create();
    $admin = User::factory()->create(['companies_id' => $Companies->id]);
    $this->actingAs($admin);

    $response = $this->postJson('/api/currencies', [
        'name' => 'Bolívar',
        'symbol' => 'VES',
        'exchange_rate' => 36.5,
        'is_base' => false,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Moneda creada correctamente ✅',
            'currency' => [
                'name' => 'Bolívar',
                'symbol' => 'VES',
                'exchange_rate' => 36.5,
                'is_base' => false,
                'companies_id' => $Companies->id,
            ],
        ]);
});

test('un admin puede actualizar una moneda', function () {
    $Companies = Companies::factory()->create();
    $admin = User::factory()->create(['companies_id' => $Companies->id]);
    $this->actingAs($admin);

    // Reemplazado Currency::factory()->create()
    $currency = Currency::create([
        'companies_id' => $Companies->id,
        'name' => 'Peso Colombiano',
        'symbol' => 'COP',
        'exchange_rate' => 4000,
    ]);

    $response = $this->putJson("/api/currencies/{$currency->id}", [
        'name' => 'Peso Colombiano Actualizado',
        'symbol' => 'COP',
        'exchange_rate' => 4200,
        'is_base' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Moneda actualizada correctamente ✅',
            'currency' => [
                'id' => $currency->id,
                'name' => 'Peso Colombiano Actualizado',
                'symbol' => 'COP',
                'exchange_rate' => 4200,
                'is_base' => false,
                'companies_id' => $Companies->id,
            ],
        ]);
});

test('un admin puede eliminar una moneda', function () {
    $Companies = Companies::factory()->create();
    $admin = User::factory()->create(['companies_id' => $Companies->id]);
    $this->actingAs($admin);

    // Reemplazado Currency::factory()->create()
    $currency = Currency::create([
        'companies_id' => $Companies->id,
        'name' => 'Moneda Temporal',
        'symbol' => 'TMP',
        'exchange_rate' => 10,
        'is_base' => false,
    ]);

    $response = $this->deleteJson("/api/currencies/{$currency->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Moneda eliminada correctamente 🗑️',
        ]);

    $this->assertDatabaseMissing('currencies', ['id' => $currency->id]);
});