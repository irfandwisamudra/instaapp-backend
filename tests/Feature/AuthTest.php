<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'John Doe',
        'username' => 'johndoe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.username', 'johndoe')
        ->assertJsonPath('data.email', 'john@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'username' => 'johndoe',
    ]);
});

test('registration validation fails for duplicate email or username', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
        'username' => 'existinguser',
    ]);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Duplicate Test',
        'username' => 'existinguser',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'email']);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'login@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.email', 'login@example.com');
});

test('login fails with invalid credentials', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'login@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can fetch current profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/user');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.username', $user->username);
});

test('user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/logout');

    $response->assertStatus(204);
});
