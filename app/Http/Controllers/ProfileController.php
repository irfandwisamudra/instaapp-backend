<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadCount(['posts', 'receivedLikes', 'receivedComments']);

        return new UserResource($user);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        // Handle avatar removal
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $validated['avatar_path'] = null;
            unset($validated['remove_avatar'], $validated['avatar']);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $oldPath = $user->avatar_path;

            $extension = $request->file('avatar')->getClientOriginalExtension();
            $newPath   = 'avatars/' . Str::uuid() . '.' . $extension;

            Storage::disk('public')->putFileAs('avatars', $request->file('avatar'), basename($newPath));

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $validated['avatar_path'] = $newPath;
            unset($validated['remove_avatar'], $validated['avatar']);
        }

        // Remove keys not on the model
        unset($validated['remove_avatar'], $validated['avatar']);

        $user->update($validated);
        $user->loadCount(['posts', 'receivedLikes', 'receivedComments']);

        return new UserResource($user);
    }

    /**
     * Display a user profile by username.
     */
    public function showByUsername(Request $request, string $username): UserResource
    {
        $user = User::query()
            ->where('username', $username)
            ->withCount(['posts', 'receivedLikes', 'receivedComments'])
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
