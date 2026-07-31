<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeletePostAction
{
    /**
     * Execute post deletion and file cleanup.
     */
    public function execute(Post $post): void
    {
        DB::transaction(function () use ($post) {
            if ($post->image_path) {
                $disk = config('filesystems.post_images_disk', 'public');
                if (Storage::disk($disk)->exists($post->image_path)) {
                    Storage::disk($disk)->delete($post->image_path);
                }
            }

            $post->delete();
        });
    }
}
