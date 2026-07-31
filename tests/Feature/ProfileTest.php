<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view own profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

test('user can view public profile by username', function () {
    $auth = User::factory()->create();
    $target = User::factory()->create(['username' => 'targetuser']);

    $response = $this->actingAs($auth)->getJson('/api/v1/users/targetuser');

    $response->assertStatus(200)
        ->assertJsonPath('data.username', 'targetuser')
        ->assertJsonMissingPath('data.email');
});

test('user can view posts by username', function () {
    $auth = User::factory()->create();
    $target = User::factory()->create(['username' => 'targetuser']);
    Post::factory(2)->create(['user_id' => $target->id]);

    $response = $this->actingAs($auth)->getJson('/api/v1/users/targetuser/posts');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('unknown username returns 404', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/users/nonexistentuser');

    $response->assertStatus(404);
});
