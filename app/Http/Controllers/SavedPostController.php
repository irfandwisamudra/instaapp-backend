<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\SavedPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SavedPostController extends Controller
{
    /**
     * Display a listing of the user's saved posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $posts = Post::query()
            ->join('saved_posts', 'posts.id', '=', 'saved_posts.post_id')
            ->where('saved_posts.user_id', $userId)
            ->select('posts.*')
            ->withFeedData($userId)
            ->orderByDesc('saved_posts.created_at')
            ->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Save/bookmark a post.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $userId = $request->user()->id;

        SavedPost::firstOrCreate([
            'user_id' => $userId,
            'post_id' => $post->id,
        ]);

        return response()->json([
            'message' => 'Post saved successfully',
            'is_saved' => true,
        ]);
    }

    /**
     * Unsave/unbookmark a post.
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $userId = $request->user()->id;

        SavedPost::where('user_id', $userId)
            ->where('post_id', $post->id)
            ->delete();

        return response()->json([
            'message' => 'Post unsaved successfully',
            'is_saved' => false,
        ]);
    }
}
