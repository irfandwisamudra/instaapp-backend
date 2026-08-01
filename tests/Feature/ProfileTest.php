<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('user can view own profile with received likes and comments count', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create 2 posts for $user
    $post1 = Post::factory()->create(['user_id' => $user->id]);
    $post2 = Post::factory()->create(['user_id' => $user->id]);

    // $otherUser likes both posts of $user (2 received likes)
    Like::factory()->create(['user_id' => $otherUser->id, 'post_id' => $post1->id]);
    Like::factory()->create(['user_id' => $otherUser->id, 'post_id' => $post2->id]);

    // $otherUser comments on post1 (1 received comment)
    Comment::factory()->create(['user_id' => $otherUser->id, 'post_id' => $post1->id]);

    // $user likes another user's post (should NOT count towards $user's received likes)
    $otherPost = Post::factory()->create(['user_id' => $otherUser->id]);
    Like::factory()->create(['user_id' => $user->id, 'post_id' => $otherPost->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.posts_count', 2)
        ->assertJsonPath('data.likes_count', 2)
        ->assertJsonPath('data.comments_count', 1);
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

test('user can update profile details', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'username' => 'originaluser',
        'bio' => 'Original Bio',
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/profile', [
        'name' => 'Updated Name',
        'username' => 'updateduser',
        'bio' => 'Updated Bio',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.username', 'updateduser')
        ->assertJsonPath('data.bio', 'Updated Bio');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'username' => 'updateduser',
        'bio' => 'Updated Bio',
    ]);
});

test('user cannot update profile with existing username', function () {
    User::factory()->create(['username' => 'takenname']);
    $user = User::factory()->create(['username' => 'myname']);

    $response = $this->actingAs($user)->postJson('/api/v1/profile', [
        'username' => 'takenname',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('user can upload avatar image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $response = $this->actingAs($user)->postJson('/api/v1/profile', [
        'avatar' => $file,
    ]);

    $response->assertStatus(200);
    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('user can remove avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar_path' => 'avatars/sample.jpg',
    ]);
    Storage::disk('public')->put('avatars/sample.jpg', 'content');

    $response = $this->actingAs($user)->postJson('/api/v1/profile', [
        'remove_avatar' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.avatar_url', null);

    $user->refresh();
    expect($user->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/sample.jpg');
});
