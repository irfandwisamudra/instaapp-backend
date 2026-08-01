<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExploreController extends Controller
{
    /**
     * Get explore posts with search and pagination.
     */
    public function posts(Request $request): AnonymousResourceCollection
    {
        $query = $request->query('q') ?? $request->query('search');
        $authUserId = $request->user()?->id;

        $posts = Post::query()
            ->withFeedData($authUserId)
            ->when($query, function ($q) use ($query) {
                $q->where('body', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(18);

        return PostResource::collection($posts);
    }

    /**
     * Get explore users / search users.
     */
    public function users(Request $request): AnonymousResourceCollection
    {
        $query = $request->query('q') ?? $request->query('search');
        $authUserId = $request->user()?->id;

        $users = User::query()
            ->withCount(['posts', 'receivedLikes', 'receivedComments'])
            ->when($authUserId, function ($q) use ($authUserId) {
                $q->where('id', '!=', $authUserId);
            })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('username', 'like', "%{$query}%")
                        ->orWhere('name', 'like', "%{$query}%");
                });
            })
            ->latest()
            ->paginate(20);

        return UserResource::collection($users);
    }
}
