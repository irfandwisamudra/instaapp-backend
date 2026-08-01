<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can fetch explore posts', function () {
    $user = User::factory()->create();
    Post::factory(5)->create();

    $response = $this->actingAs($user)->getJson('/api/v1/explore/posts');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'body', 'image_url', 'likes_count', 'comments_count', 'user'],
            ],
            'meta',
            'links',
        ]);
});

test('authenticated user can search explore posts by query', function () {
    $user = User::factory()->create();
    Post::factory()->create(['body' => 'Sunset photo in Bali']);
    Post::factory()->create(['body' => 'Mountain hiking adventure']);

    $response = $this->actingAs($user)->getJson('/api/v1/explore/posts?q=Bali');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.body', 'Sunset photo in Bali');
});

test('authenticated user can fetch explore users', function () {
    $user = User::factory()->create();
    User::factory(3)->create();

    $response = $this->actingAs($user)->getJson('/api/v1/explore/users');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('authenticated user can search explore users by name or username', function () {
    $user = User::factory()->create();
    User::factory()->create(['name' => 'Alice Wonder', 'username' => 'alice']);
    User::factory()->create(['name' => 'Bob Builder', 'username' => 'bob']);

    $response = $this->actingAs($user)->getJson('/api/v1/explore/users?q=Alice');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.username', 'alice');
});

test('unauthenticated user cannot access explore endpoints', function () {
    $this->getJson('/api/v1/explore/posts')->assertStatus(401);
    $this->getJson('/api/v1/explore/users')->assertStatus(401);
});
