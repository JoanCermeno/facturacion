<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Companies;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_admin_puede_crear_un_cajero()
    {
        $company = Companies::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
            'companies_id' => $company->id,
        ]);

        $response = $this->actingAs($admin)->postJson('/api/cashiers', [
            'name' => 'Juan Cajero',
            'email' => 'cajero@example.com',
            'password' => 'password123',
            'phone' => '123456789',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Cajero creado correctamente ✅',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'cajero@example.com',
            'role' => 'cashier',
            'companies_id' => $company->id,
        ]);
    }

    /** @test */
    public function un_usuario_no_admin_no_puede_crear_cajeros()
    {
        $company = Companies::factory()->create();
        $user = User::factory()->create([
            'role' => 'cashier',
            'companies_id' => $company->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/cashiers', [
            'name' => 'Juanito',
            'email' => 'juanito@example.com',
            'password' => 'password123',
            'phone' => '123456789',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Solo un admin puede crear cajeros.',
            ]);
    }

    /** @test */
    public function un_admin_sin_empresa_no_puede_crear_cajeros()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'companies_id' => null,
        ]);

        $response = $this->actingAs($admin)->postJson('/api/cashiers', [
            'name' => 'Pepe',
            'email' => 'pepe@example.com',
            'password' => 'password123',
            'phone' => '123456789',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'El admin no tiene una empresa asociada.',
            ]);
    }

    /** @test */
    public function un_admin_puede_listar_los_cajeros_de_su_empresa()
    {
        $company = Companies::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
            'companies_id' => $company->id,
        ]);

        $cashiers = User::factory()->count(3)->create([
            'role' => 'cashier',
            'companies_id' => $company->id,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/cashiers');

        $response->assertStatus(200)
            ->assertJsonCount(3); // 3 cajeros listados
    }
}
