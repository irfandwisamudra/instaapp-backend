<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can list comments for a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Comment::factory(3)->create(['post_id' => $post->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/posts/{$post->id}/comments");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can add a comment to a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/v1/posts/{$post->id}/comments", [
        'body' => 'Great photo!',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.body', 'Great photo!')
        ->assertJsonPath('data.can_update', true);

    $this->assertDatabaseHas('comments', [
        'user_id' => $user->id,
        'post_id' => $post->id,
        'body' => 'Great photo!',
    ]);
});

test('whitespace-only comment fails validation', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/v1/posts/{$post->id}/comments", [
        'body' => '   ',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['body']);
});

test('comment author can update comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id, 'body' => 'Original comment']);

    $response = $this->actingAs($user)->patchJson("/api/v1/comments/{$comment->id}", [
        'body' => 'Edited comment',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.body', 'Edited comment');
});

test('post owner can delete any comment on their post', function () {
    $postOwner = User::factory()->create();
    $commentAuthor = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $postOwner->id]);
    $comment = Comment::factory()->create(['user_id' => $commentAuthor->id, 'post_id' => $post->id]);

    $response = $this->actingAs($postOwner)->deleteJson("/api/v1/comments/{$comment->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('unrelated user cannot delete comment', function () {
    $postOwner = User::factory()->create();
    $commentAuthor = User::factory()->create();
    $unrelatedUser = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $postOwner->id]);
    $comment = Comment::factory()->create(['user_id' => $commentAuthor->id, 'post_id' => $post->id]);

    $response = $this->actingAs($unrelatedUser)->deleteJson("/api/v1/comments/{$comment->id}");

    $response->assertStatus(403);
});
