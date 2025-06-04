<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostComment>
 */
class PostCommentFactory extends Factory
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
            'post_id' => '01jhj1gdtqnvre02nkwsr5qk7c',
            // 'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            // 'status' => $this->faker->randomElement(['active', 'disabled', 'pending']),
        ];
    }
}
