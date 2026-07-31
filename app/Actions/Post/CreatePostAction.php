<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreatePostAction
{
    /**
     * Execute post creation.
     */
    public function execute(User $user, array $data, ?UploadedFile $image = null): Post
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $imagePath = null;

            if ($image) {
                $disk = config('filesystems.post_images_disk', 'public');
                $imagePath = $image->store('posts', $disk);
            }

            return Post::create([
                'user_id' => $user->id,
                'body' => $data['body'] ?? null,
                'image_path' => $imagePath,
            ]);
        });
    }
}
