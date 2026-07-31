<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('authenticated user can view posts feed', function () {
    $user = User::factory()->create();
    Post::factory(3)->create();

    $response = $this->actingAs($user)->getJson('/api/v1/posts');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'user', 'body', 'image_url', 'likes_count', 'comments_count', 'liked_by_user', 'can_update', 'can_delete'],
            ],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('authenticated user can create a text post', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/posts', [
        'body' => 'My first post!',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.body', 'My first post!')
        ->assertJsonPath('data.can_update', true);

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'body' => 'My first post!',
    ]);
});

test('authenticated user can upload a post image', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('photo.jpg', 600, 600);

    $response = $this->actingAs($user)->postJson('/api/v1/posts', [
        'body' => 'Photo post',
        'image' => $file,
    ]);

    $response->assertStatus(201);
    $post = Post::latest('id')->first();
    expect($post->image_path)->not()->toBeNull();
    Storage::disk('public')->assertExists($post->image_path);
});

test('post creation fails if both body and image are missing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/posts', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['body']);
});

test('post owner can update post body', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id, 'body' => 'Old content']);

    $response = $this->actingAs($user)->patchJson("/api/v1/posts/{$post->id}", [
        'body' => 'Updated content',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.body', 'Updated content');
});

test('non owner cannot update post', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id, 'body' => 'Original']);

    $response = $this->actingAs($otherUser)->patchJson("/api/v1/posts/{$post->id}", [
        'body' => 'Hacked body',
    ]);

    $response->assertStatus(403);
});

test('post owner can delete post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/posts/{$post->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});
