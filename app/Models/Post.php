<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'body',
        'image_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $disk = config('filesystems.post_images_disk', 'public');

        return Storage::disk($disk)->url($this->image_path);
    }

    /**
     * Scope query with feed counts and auth user like status.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeWithFeedData(Builder $query, ?int $authUserId): Builder
    {
        return $query
            ->with(['user:id,name,username,avatar_path'])
            ->withCount(['likes', 'comments'])
            ->when($authUserId, function (Builder $q) use ($authUserId) {
                $q->withExists([
                    'likes as liked_by_user' => function (Builder $likeQuery) use ($authUserId) {
                        $likeQuery->where('user_id', $authUserId);
                    },
                ]);
            });
    }
}
