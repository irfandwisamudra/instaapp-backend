<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can save and unsave a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    // Save post
    $response = $this->actingAs($user)->postJson("/api/v1/posts/{$post->id}/save");

    $response->assertStatus(200)
        ->assertJsonPath('is_saved', true);

    $this->assertDatabaseHas('saved_posts', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);

    // Check feed data includes is_saved = true
    $feedResponse = $this->actingAs($user)->getJson('/api/v1/posts');
    $feedResponse->assertStatus(200)
        ->assertJsonPath('data.0.is_saved', true);

    // Unsave post
    $unsaveResponse = $this->actingAs($user)->deleteJson("/api/v1/posts/{$post->id}/save");

    $unsaveResponse->assertStatus(200)
        ->assertJsonPath('is_saved', false);

    $this->assertDatabaseMissing('saved_posts', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('user can list saved posts', function () {
    $user = User::factory()->create();
    $posts = Post::factory(3)->create();

    // Save first two posts
    $this->actingAs($user)->postJson("/api/v1/posts/{$posts[0]->id}/save");
    $this->actingAs($user)->postJson("/api/v1/posts/{$posts[1]->id}/save");

    $response = $this->actingAs($user)->getJson('/api/v1/saved-posts');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});
