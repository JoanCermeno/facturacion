<?php

use App\Models\User;
use App\Models\Companies;
use App\Models\Product;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\CashRegister;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un cajero puede crear una venta que afecta el inventario', function () {
    $company = Companies::factory()->create([
        'invoice_sequence' => 100, // Se espera que la primera factura sea 100
        'auto_code_products' => true,
    ]);

    $user = User::factory()->create([
        'companies_id' => $company->id,
        'role' => 'cashier',
    ]);

    $currencyBase = Currency::factory()->create([
        'companies_id' => $company->id,
        'is_base' => true,
        'exchange_rate' => 1
    ]);

    $paymentMethod = PaymentMethod::factory()->create([
        'companies_id' => $company->id,
        'currency_id' => $currencyBase->id,
    ]);

    $department = Department::factory()->create(['companies_id' => $company->id]);

    $product = Product::factory()->create([
        'companies_id' => $company->id,
        'department_id' => $department->id,
        'currency_id' => $currencyBase->id,
        'stock' => 50,
        'cost' => 10.00,
    ]);

    $register = CashRegister::create([
        'user_id' => $user->id,
        'companies_id' => $company->id,
        'status' => 'open',
    ]);

    $this->actingAs($user)
        ->postJson('/api/sales', [
            'cash_register_id' => $register->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ],
            'payments' => [
                [
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => 20.00,
                ]
            ]
        ])
        ->assertStatus(201)
        ->assertJsonFragment([
            'invoice_number' => 'FAC-000100',
            'product_name' => $product->name,
            'quantity' => 2,
            'subtotal' => 20.00,
            'total' => 20.00,
        ]);

    // Verificar en BD que el stock se descontó (50 - 2 = 48)
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 48,
    ]);

    $this->assertDatabaseHas('invoices', [
        'invoice_number' => 100,
        'company_id' => $company->id,
    ]);

    // Asegurar que el sequence correlativo avanzó
    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'invoice_sequence' => 101, // Porque se creó 100 y luego avanzó
    ]);
});

test('Listar ventas funciona con estructura de transformacion', function () {
    $company = Companies::factory()->create();
    $user = User::factory()->create(['companies_id' => $company->id]);
    
    $this->actingAs($user)
        ->getJson('/api/sales')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
        ]);
});
