<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::factory()->create([
            'name' => 'Demo User',
            'username' => 'demouser',
            'email' => 'demo@example.com',
            'bio' => 'Welcome to InstaApp! Photo sharing & social feed.',
        ]);

        $users = User::factory(5)->create();

        $allUsers = $users->concat([$demoUser]);

        foreach ($allUsers as $user) {
            $posts = Post::factory(3)->create(['user_id' => $user->id]);

            foreach ($posts as $post) {
                // Add 1-3 comments per post
                $commenters = $allUsers->random(rand(1, 3));
                foreach ($commenters as $commenter) {
                    Comment::factory()->create([
                        'user_id' => $commenter->id,
                        'post_id' => $post->id,
                    ]);
                }

                // Add 1-4 likes per post
                $likers = $allUsers->random(rand(1, 4));
                foreach ($likers as $liker) {
                    Like::factory()->create([
                        'user_id' => $liker->id,
                        'post_id' => $post->id,
                    ]);
                }
            }
        }
    }
}
