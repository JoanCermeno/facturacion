<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can register', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Joan',
        'email' => 'joan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJson([
                 'message' => 'Usuario registrado correctamente.',
             ]);

    $this->assertDatabaseHas('users', [
        'email' => 'joan@example.com',
    ]);
});

test('a user can login and receive a token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'token',
                 'user' => ['id', 'name', 'email'],
             ]);
});
