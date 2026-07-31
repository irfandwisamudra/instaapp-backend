<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadCount(['posts', 'likes', 'comments']);

        return new UserResource($user);
    }

    /**
     * Display a user profile by username.
     */
    public function showByUsername(Request $request, string $username): UserResource
    {
        $user = User::query()
            ->where('username', $username)
            ->withCount(['posts', 'likes', 'comments'])
            ->firstOrFail();

        return new UserResource($user);
    }

    /**
     * Display posts by a specific user (by username).
     */
    public function userPosts(Request $request, string $username): AnonymousResourceCollection
    {
        $user = User::query()
            ->where('username', $username)
            ->firstOrFail();

        $authUserId = $request->user()?->id;

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->withFeedData($authUserId)
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }
}
