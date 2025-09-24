<?php

use App\Models\User;
use App\Models\Companies;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede ver su propia compania', function () {
    $company = Companies::factory()->create([
        'name' => 'Mi Empresa',
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $this->actingAs($admin)
        ->getJson('/api/my-company')
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'Mi Empresa']);
});

test('un admin puede actualizar su propia compania', function () {
    $company = Companies::factory()->create([
        'name' => 'Viejo Nombre',
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $this->actingAs($admin)
        ->putJson('/api/my-company', [
            'name' => 'Nuevo Nombre',
            'address' => 'Nueva dirección',
            'phone' => '0412-9876543',
            'email' => 'empresa@ejemplo.com',
            'rif' => 'J-12345678-9',
            'invoice_sequence' => 1001,
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'Nuevo Nombre']);

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'Nuevo Nombre',
    ]);
});
