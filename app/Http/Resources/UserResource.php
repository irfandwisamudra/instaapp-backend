<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'username'       => $this->username,
            'email'          => $this->when($request->user()?->id === $this->id, $this->email),
            'avatar_url'     => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
            'bio'            => $this->bio,
            'posts_count'    => $this->whenCounted('posts', $this->posts_count),
            'likes_count'    => $this->whenCounted('receivedLikes', $this->received_likes_count, $this->whenCounted('likes', $this->likes_count)),
            'comments_count' => $this->whenCounted('receivedComments', $this->received_comments_count, $this->whenCounted('comments', $this->comments_count)),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
