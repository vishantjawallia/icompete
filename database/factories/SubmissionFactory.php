<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contest = Contest::inRandomOrder()->first() ?? Contest::factory()->create();
        $requirements = collect($contest->requirements ?? []);

        $titles = [
            'My Best Performance',
            'Creative Artwork Submission',
            'Dance Battle Entry',
            'Singing Competition Performance',
            'Ultimate Talent Showcase',
            'Photography Contest Entry',
            'Innovative Idea Presentation',
            'Cooking Masterpiece Entry',
            'Short Film Submission',
            'Musical Instrument Performance',
        ];
        // Generate a response based on the contest's requirements
        $response = $requirements->map(function ($requirement) {
            $value = match ($requirement['type']) {
                'text'  => fake()->sentence(3), // Simple text response
                'image' => 'participants/images/1738046973-oKbP71pNhbVEQ8QwYIYlpQW2IS.png', // Fake image path
                'video' => 'participants/videos/default.mp4', // Fake video path
                default => null,
            };

            return [
                'name'  => $requirement['name'],
                'type'  => $requirement['type'],
                'value' => $value,
            ];
        })->toArray();
        $sType = collect(['entry', 'submission'])->random();
        $status = collect(['pending', 'approved', 'enabled', 'disabled'])->random();
        $vStatus = collect(['enabled', 'disabled'])->random();

        return [
            'user_id'          => User::whereRole('contestant')->inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'contest_id'       => $contest->id,
            'type'             => $sType,
            'title'            => \Str::random(12) . '' . fake()->randomElement($titles),
            'description'      => fake()->optional()->paragraph(3),
            'vote_count'       => fake()->numberBetween(0, 1000),
            'is_winner'        => fake()->boolean(10), // 10% chance of being a winner
            'status'           => $status,
            'vote_status'      => $vStatus,
            'response'         => $response,
            'rejection_reason' => fake()->optional(0.2)->sentence(6), // 20% chance of having a rejection reason
            'created_at'       => now()->subDays(fake()->numberBetween(1, 30)),
            'updated_at'       => now(),
        ];
    }
}
