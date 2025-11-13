<?php

use App\Models\User;
use App\Models\Companies;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede crear un seller', function () {
    // Crear empresa (modelo Companies)
    $company = Companies::factory()->create();

    // Crear usuario admin y asociarlo a la empresa
    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    // Hacer la petición autenticada para crear vendedor
    $response = $this->actingAs($admin)->postJson('/api/sellers', [
        'ci' => '12345678',
        'name' => 'Pedro Pérez',
        'phone' => '0414-1234567',
        'commission' => 7.5,
        'commission_type' => 'utility',
    ]);

    // Comprobaciones
    $response->assertStatus(201)
             ->assertJson([
                 'message' => 'Vendedor registrado correctamente ✅',
                 'seller' => [
                     'ci' => '12345678',
                     'name' => 'Pedro Pérez',
                     'companies_id' => $company->id,
                     'phone' => '0414-1234567',
                     'commission' => 7.5,
                     'commission_type' => 'utility',
                 ]
             ]);

    $this->assertDatabaseHas('sellers', [
        'ci' => '12345678',
        'companies_id' => $company->id,
    ]);
});
