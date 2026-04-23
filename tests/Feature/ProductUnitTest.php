<?php

use App\Models\User;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Companies;
use App\Models\Department;
use App\Models\Currency;
use App\Models\PriceType;
use App\Models\ProductPrice;
use App\Models\PriceHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Companies::factory()->create();
    $this->user = User::factory()->create(['companies_id' => $this->company->id]);
    $this->department = Department::factory()->create(['companies_id' => $this->company->id]);
    $this->currency = Currency::factory()->create();

    $this->product = Product::factory()->create([
        'companies_id' => $this->company->id,
        'department_id' => $this->department->id,
        'currency_id' => $this->currency->id,
        'cost' => 10, // El costo base es 10
        'base_unit' => 'Unit',
    ]);

    // 🛠️ CAMBIO CLAVE: Creamos los tipos de precio atados a la empresa para el Multitenant
    $this->priceType1 = PriceType::create(['companies_id' => $this->company->id, 'name' => 'Contado', 'slug' => 'contado']);
    $this->priceType2 = PriceType::create(['companies_id' => $this->company->id, 'name' => 'Mayor', 'slug' => 'mayor']);
});

test('can list product units', function () {
    ProductUnit::create([
        'product_id' => $this->product->id,
        'unit_type' => 'Unit',
        'conversion_factor' => 1,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/product-units?product_id=' . $this->product->id);

    // 🛠️ CAMBIO CLAVE: Ahora leemos 'data' porque el controlador usa paginate()
    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'data', 'current_page', 'total'])
        ->assertJsonCount(1, 'data');
});

test('can create a product unit and it generates dynamic prices', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/product-units', [
        'product_id' => $this->product->id,
        'unit_type' => 'Box',
        'conversion_factor' => 12,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('product_unit.unit_type', 'Box');

    $unitId = $response->json('product_unit.id');

    // Verificamos que se crearon los precios dinámicos atados a los PriceTypes de la empresa
    $this->assertDatabaseHas('product_prices', [
        'product_unit_id' => $unitId,
        'price_type_id' => $this->priceType1->id,
    ]);
});

test('cannot create a duplicate product unit type', function () {
    ProductUnit::create([
        'product_id' => $this->product->id,
        'unit_type' => 'Box', // Asumiendo que el mutator o el frontend lo limpia a mayúscula
        'conversion_factor' => 12,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/product-units', [
        'product_id' => $this->product->id,
        'unit_type' => 'Box',
        'conversion_factor' => 24,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Esta presentación ya existe para el producto.');
});

test('can update a product unit and it recalculates prices', function () {
    // 1. Creamos la unidad y sus precios (Simulando lo que hace el store)
    $unit = ProductUnit::create([
        'product_id' => $this->product->id,
        'unit_type' => 'Box',
        'conversion_factor' => 10, // Costo total será 10 * 10 = 100
    ]);

    $price = ProductPrice::create([
        'product_unit_id' => $unit->id,
        'price_type_id' => $this->priceType1->id,
        'price_usd' => 130, // Asumiendo un margen guardado previamente
        'profit_percentage' => 30,
    ]);

    // 2. Hacemos el UPDATE cambiando el factor de conversión a 20
    $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/product-units/{$unit->id}", [
        'conversion_factor' => 20,
    ]);

    $response->assertStatus(200);

    // 3. Verificamos que se actualizó el factor
    $this->assertDatabaseHas('product_units', [
        'id' => $unit->id,
        'conversion_factor' => 20,
    ]);

    // 4. 🛠️ PRUEBA MAGISTRAL: Verificamos que el precio se recalculó
    // Nuevo costo: 10 (costo producto) * 20 (nuevo factor) = 200
    // Nuevo precio con 30% ganancia: 200 * 1.30 = 260
    $this->assertDatabaseHas('product_prices', [
        'id' => $price->id,
        'price_usd' => 260,
    ]);

    // 5. Verificamos que se dejó el rastro en el historial
    $this->assertDatabaseHas('price_histories', [
        'product_price_id' => $price->id,
        'old_price' => 130,
        'new_price' => 260,
    ]);
});

test('can delete a product unit, but not the base one', function () {
    $baseUnit = ProductUnit::create([
        'product_id' => $this->product->id,
        'unit_type' => 'Unit',
        'conversion_factor' => 1,
    ]);

    $boxUnit = ProductUnit::create([
        'product_id' => $this->product->id,
        'unit_type' => 'Box',
        'conversion_factor' => 12,
    ]);

    $response1 = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/product-units/{$baseUnit->id}");
    $response1->assertStatus(422)
        ->assertJsonPath('message', 'No se puede eliminar la presentación base del producto.');

    $response2 = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/product-units/{$boxUnit->id}");
    $response2->assertStatus(200);

    $this->assertDatabaseMissing('product_units', [
        'id' => $boxUnit->id,
    ]);
});