<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede ver las monedas de su empresa', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $currency = Currency::create([
        'companies_id' => $company->id,
        'name' => 'Dólar Americano',
        'symbol' => 'USD',
        'exchange_rate' => 1,
        'is_base' => true,
    ]);

    $response = $this->actingAs($admin)->getJson('/api/currencies');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Dólar Americano',
            'symbol' => 'USD',
            'exchange_rate' => 1,
            'is_base' => true,
            'companies_id' => $company->id,
        ]);
});

test('un admin puede crear una moneda', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $response = $this->actingAs($admin)->postJson('/api/currencies', [
        'name' => 'Bolívar Venezolano',
        'symbol' => 'VES',
        'exchange_rate' => 36.5,
        'is_base' => false,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Moneda creada correctamente ✅',
            'currency' => [
                'name' => 'Bolívar Venezolano',
                'symbol' => 'VES',
                'exchange_rate' => 36.5,
                'is_base' => false,
                'companies_id' => $company->id,
            ]
        ]);

    $this->assertDatabaseHas('currencies', [
        'symbol' => 'VES',
        'companies_id' => $company->id,
    ]);
});

test('un admin puede actualizar una moneda', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $currency = Currency::create([
        'companies_id' => $company->id,
        'name' => 'Peso Colombiano',
        'symbol' => 'COP',
        'exchange_rate' => 4200,
        'is_base' => false,
    ]);

    $response = $this->actingAs($admin)->putJson("/api/currencies/{$currency->id}", [
        'exchange_rate' => 4300,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'exchange_rate' => 4300,
        ]);

    $this->assertDatabaseHas('currencies', [
        'id' => $currency->id,
        'exchange_rate' => 4300,
    ]);
});

test('un admin puede eliminar una moneda', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $currency = Currency::create([
        'companies_id' => $company->id,
        'name' => 'Euro',
        'symbol' => 'EUR',
        'exchange_rate' => 0.9,
        'is_base' => false,
    ]);

    $response = $this->actingAs($admin)->deleteJson("/api/currencies/{$currency->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Moneda eliminada correctamente ✅',
        ]);

    $this->assertDatabaseMissing('currencies', [
        'id' => $currency->id,
    ]);
});
