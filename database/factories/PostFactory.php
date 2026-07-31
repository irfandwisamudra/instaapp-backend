<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
/**
 * @extends Factory<Post>
 */
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'image_path' => null,
        ];
    }

    /**
     * Indicate that the post contains only text.
     */
    public function textOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => null,
        ]);
    }

    /**
     * Indicate that the post contains an image.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => 'posts/demo.jpg',
        ]);
    }
}
