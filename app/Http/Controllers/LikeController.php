<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LikeController extends Controller
{
    /**
     * Store a like for the post.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $this->authorize('create', Like::class);

        $userId = $request->user()->id;

        try {
            Like::create([
                'user_id' => $userId,
                'post_id' => $post->id,
            ]);
        } catch (QueryException $e) {
            // Duplicate key error handler -> return 409 Conflict
            return response()->json([
                'message' => 'Post already liked.',
            ], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'data' => [
                'post_id' => $post->id,
                'likes_count' => $post->likes()->count(),
                'liked_by_user' => true,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Remove a like from the post.
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $userId = $request->user()->id;

        $like = Like::query()
            ->where('user_id', $userId)
            ->where('post_id', $post->id)
            ->firstOrFail();

        $this->authorize('delete', $like);

        $like->delete();

        return response()->json([
            'data' => [
                'post_id' => $post->id,
                'likes_count' => $post->likes()->count(),
                'liked_by_user' => false,
            ],
        ], Response::HTTP_OK);
    }
}
