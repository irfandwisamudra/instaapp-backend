<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UpdatePostAction
{
    /**
     * Execute post update.
     */
    public function execute(Post $post, array $data, ?UploadedFile $image = null, bool $removeImage = false): Post
    {
        return DB::transaction(function () use ($post, $data, $image, $removeImage) {
            $disk = config('filesystems.post_images_disk', 'public');
            $newImagePath = $post->image_path;

            if ($image) {
                if ($post->image_path && Storage::disk($disk)->exists($post->image_path)) {
                    Storage::disk($disk)->delete($post->image_path);
                }
                $newImagePath = $image->store('posts', $disk);
            } elseif ($removeImage && $post->image_path) {
                if (Storage::disk($disk)->exists($post->image_path)) {
                    Storage::disk($disk)->delete($post->image_path);
                }
                $newImagePath = null;
            }

            $newBody = array_key_exists('body', $data) ? $data['body'] : $post->body;

            if (blank($newBody) && empty($newImagePath)) {
                throw ValidationException::withMessages([
                    'body' => ['At least one of body or image must remain on the post.'],
                ]);
            }

            $post->update([
                'body' => $newBody,
                'image_path' => $newImagePath,
            ]);

            return $post->fresh();
        });
    }
}
