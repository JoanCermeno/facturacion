<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\PaymentMethod;
use App\Models\CashRegister;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un usuario puede abrir una caja con multiples metodos de pago', function () {
    $company = Companies::factory()->create();
    
    // El usuario debe estar vinculado a la empresa
    $user = User::factory()->create([
        'companies_id' => $company->id,
        'role' => 'cashier',
    ]);

    // Crear moneda base para el método de pago
    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
        'is_base' => true,
        'exchange_rate' => 1
    ]);

    $paymentMethod1 = PaymentMethod::factory()->create(['companies_id' => $company->id, 'currency_id' => $currency->id]);
    $paymentMethod2 = PaymentMethod::factory()->create(['companies_id' => $company->id, 'currency_id' => $currency->id]);

    $this->actingAs($user)
        ->postJson('/api/cash-registers', [
            'details' => [
                ['payment_method_id' => $paymentMethod1->id, 'initial_amount' => 100.50],
                ['payment_method_id' => $paymentMethod2->id, 'initial_amount' => 50.00],
            ],
            'notes' => 'Apertura de turno mañana'
        ])
        ->assertStatus(201)
        ->assertJsonFragment(['notes' => 'Apertura de turno mañana'])
        ->assertJsonFragment(['initial_amount' => 100.50])
        ->assertJsonFragment(['initial_amount' => 50.00]);

    $this->assertDatabaseHas('cash_registers', [
        'user_id' => $user->id,
        'status' => 'open',
    ]);

    $this->assertDatabaseCount('cash_register_details', 2);
});

test('un usuario no puede abrir dos cajas al mismo tiempo', function () {
    $company = Companies::factory()->create();
    
    $user = User::factory()->create([
        'companies_id' => $company->id,
    ]);

    // Crear una caja ya abierta
    CashRegister::create([
        'user_id' => $user->id,
        'companies_id' => $company->id,
        'status' => 'open',
    ]);

    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
        'is_base' => true,
    ]);
    
    $paymentMethod = PaymentMethod::factory()->create(['companies_id' => $company->id, 'currency_id' => $currency->id]);

    $this->actingAs($user)
        ->postJson('/api/cash-registers', [
            'details' => [
                ['payment_method_id' => $paymentMethod->id, 'initial_amount' => 100],
            ],
        ])
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Ya tienes una caja abierta. Ciérrala antes de abrir otra.']);
});

test('un usuario puede cerrar su caja abierta', function () {
    $company = Companies::factory()->create();
    
    $user = User::factory()->create([
        'companies_id' => $company->id,
    ]);

    $currency = Currency::factory()->create([
        'companies_id' => $company->id,
        'is_base' => true,
    ]);

    $paymentMethod = PaymentMethod::factory()->create(['companies_id' => $company->id, 'currency_id' => $currency->id]);

    $register = CashRegister::create([
        'user_id' => $user->id,
        'companies_id' => $company->id,
        'status' => 'open',
    ]);

    // Detalle inicial
    $register->details()->create([
        'payment_method_id' => $paymentMethod->id,
        'initial_amount' => 100,
    ]);

    $this->actingAs($user)
        ->postJson("/api/cash-registers/{$register->id}/close", [
            'details' => [
                ['payment_method_id' => $paymentMethod->id, 'final_amount' => 150.00],
            ],
            'notes' => 'Cierre con cuadre perfecto'
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['final_amount' => 150.00]);

    $this->assertDatabaseHas('cash_registers', [
        'id' => $register->id,
        'status' => 'closed',
    ]);

    $this->assertDatabaseHas('cash_register_details', [
        'cash_register_id' => $register->id,
        'final_amount' => 150.00,
    ]);
});

test('Listar la caja activa actual (current)', function () {
    $company = Companies::factory()->create();
    $user = User::factory()->create(['companies_id' => $company->id]);
    
    $register = CashRegister::create([
        'user_id' => $user->id,
        'companies_id' => $company->id,
        'status' => 'open',
        'notes' => 'Caja de prueba'
    ]);

    $this->actingAs($user)
        ->getJson('/api/cash-registers/current')
        ->assertStatus(200)
        ->assertJsonFragment(['notes' => 'Caja de prueba']);
});
