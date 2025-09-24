<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un admin puede crear un departamento', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $response = $this->actingAs($admin)->postJson('/api/departments', [
        'code' => 'DEP001',
        'description' => 'Electrónica',
        'type' => 'unit',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Departamento creado correctamente ✅',
            'department' => [
                'code' => 'DEP001',
                'description' => 'Electrónica',
                'type' => 'unit',
                'companies_id' => $company->id,
            ]
        ]);

    $this->assertDatabaseHas('departments', [
        'code' => 'DEP001',
        'companies_id' => $company->id,
    ]);
});

test('un admin puede editar un departamento', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $department = Department::factory()->create([
        'companies_id' => $company->id,
        'code' => 'DEP002',
        'description' => 'Ropa',
        'type' => 'unit',
    ]);

    $response = $this->actingAs($admin)->putJson("/api/departments/{$department->id}", [
        'description' => 'Ropa y accesorios',
        'type' => 'service',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'description' => 'Ropa y accesorios',
            'type' => 'service',
        ]);

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'description' => 'Ropa y accesorios',
        'type' => 'service',
    ]);
});

test('un admin puede eliminar un departamento', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $department = Department::factory()->create([
        'companies_id' => $company->id,
        'code' => 'DEP003',
        'description' => 'Juguetes',
        'type' => 'unit',
    ]);

    $response = $this->actingAs($admin)->deleteJson("/api/departments/{$department->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Departamento eliminado correctamente 🗑️',
        ]);

    $this->assertDatabaseMissing('departments', [
        'id' => $department->id,
    ]);
});
