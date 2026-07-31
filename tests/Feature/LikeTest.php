<?php

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can like a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/v1/posts/{$post->id}/likes");

    $response->assertStatus(201)
        ->assertJsonPath('data.liked_by_user', true)
        ->assertJsonPath('data.likes_count', 1);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('duplicate like returns 409 conflict', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Like::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

    $response = $this->actingAs($user)->postJson("/api/v1/posts/{$post->id}/likes");

    $response->assertStatus(409);
});

test('user can unlike a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Like::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/posts/{$post->id}/likes");

    $response->assertStatus(200)
        ->assertJsonPath('data.liked_by_user', false)
        ->assertJsonPath('data.likes_count', 0);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});
