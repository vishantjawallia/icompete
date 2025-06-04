<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
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
            'user_id' => User::inRandomOrder()->first()->id,
            'type'    => $this->faker->randomElement(['update', 'discussion']),
            'title'   => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'image'   => $this->faker->imageUrl(),
            // 'status' => $this->faker->randomElement(['active', 'disabled', 'pending']),
            'likes_count'    => $this->faker->numberBetween(0, 100),
            'comments_count' => $this->faker->numberBetween(0, 20),
        ];
    }
}
