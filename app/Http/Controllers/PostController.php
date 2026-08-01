<?php

namespace App\Http\Controllers;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PostController extends Controller
{
    /**
     * Display a listing of posts for feed.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Post::class);

        $authUserId = $request->user()?->id;

        $posts = Post::query()
            ->withFeedData($authUserId)
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request, CreatePostAction $action): JsonResponse
    {
        $this->authorize('create', Post::class);

        $post = $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        $post->load(['user'])->loadCount(['likes', 'comments']);
        $post->liked_by_user = false;
        $post->is_saved = false;

        return (new PostResource($post))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified post.
     */
    public function show(Request $request, Post $post): PostResource
    {
        $this->authorize('view', $post);

        $authUserId = $request->user()?->id;

        $post->load(['user'])->loadCount(['likes', 'comments']);
        $post->liked_by_user = $authUserId ? $post->likes()->where('user_id', $authUserId)->exists() : false;
        $post->is_saved = $authUserId ? $post->savedByUsers()->where('user_id', $authUserId)->exists() : false;

        return new PostResource($post);
    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): PostResource
    {
        $this->authorize('update', $post);

        $updated = $action->execute(
            $post,
            $request->validated(),
            $request->file('image'),
            $request->boolean('remove_image')
        );

        $authUserId = $request->user()?->id;
        $updated->load(['user'])->loadCount(['likes', 'comments']);
        $updated->liked_by_user = $authUserId ? $updated->likes()->where('user_id', $authUserId)->exists() : false;
        $updated->is_saved = $authUserId ? $updated->savedByUsers()->where('user_id', $authUserId)->exists() : false;

        return new PostResource($updated);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post, DeletePostAction $action): Response
    {
        $this->authorize('delete', $post);

        $action->execute($post);

        return response()->noContent();
    }
}
