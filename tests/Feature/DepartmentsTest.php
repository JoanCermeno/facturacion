<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// 📘 Listar departamentos
test('un admin puede listar los departamentos de su empresa', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    Department::factory()->create([
        'companies_id' => $company->id,
        'code' => 'DEP-001',
        'description' => 'Viveres',
        'type' => 'unit',
    ]);

    $response = $this->actingAs($admin)->getJson('/api/departments');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'code' => 'DEP-001',
            'description' => 'Viveres',
            'type' => 'unit',
            'companies_id' => $company->id,
        ]);
});

// 📗 Crear un nuevo departamento
test('un admin puede crear un departamento', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $data = [
        'code' => 'DEP-002',
        'description' => 'Ferretería',
        'type' => 'unit',
    ];

    $response = $this->actingAs($admin)->postJson('/api/departments', $data);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Departamento creado correctamente ✅',
            'department' => [
                'code' => 'DEP-002',
                'description' => 'Ferretería',
                'type' => 'unit',
                'companies_id' => $company->id,
            ]
        ]);

    $this->assertDatabaseHas('departments', [
        'code' => 'DEP-002',
        'companies_id' => $company->id,
    ]);
});

// 📙 Actualizar un departamento
test('un admin puede actualizar un departamento', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $department = Department::factory()->create([
        'companies_id' => $company->id,
        'code' => 'DEP-003',
        'description' => 'Bebidas',
        'type' => 'unit',
    ]);

    $response = $this->actingAs($admin)->putJson("/api/departments/{$department->id}", [
        'description' => 'Bebidas y Refrescos',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'description' => 'Bebidas y Refrescos',
        ]);

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'description' => 'Bebidas y Refrescos',
    ]);
});

// 📕 Eliminar un departamento
test('un admin puede eliminar un departamento', function () {
    $company = Companies::factory()->create();

    $admin = User::factory()->create([
        'role' => 'admin',
        'companies_id' => $company->id,
    ]);

    $department = Department::factory()->create([
        'companies_id' => $company->id,
        'code' => 'DEP-004',
        'description' => 'Aseo Personal',
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

// 📒 Validación: no puede crear sin empresa asociada
test('no se puede crear un departamento sin empresa asociada', function () {
    $userSinEmpresa = User::factory()->create([
        'companies_id' => null,
    ]);

    $response = $this->actingAs($userSinEmpresa)->postJson('/api/departments', [
        'code' => 'DEP-999',
        'description' => 'Sin empresa',
        'type' => 'unit',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Debes tener una empresa registrada.',
        ]);
});
