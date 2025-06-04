<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contest>
 */
class ContestFactory extends Factory
{
    protected $model = Contest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');
        $votingStartDate = fake()->dateTimeBetween($startDate, $endDate);
        $votingEndDate = fake()->dateTimeBetween($votingStartDate, $endDate);
        $titles = [
            'Talent Unleashed: Show Your Best Skills!',
            'iCompete Star: The Ultimate Challenge',
            'Master of Creativity: Prove Your Genius',
            'The Grand Showdown: Compete & Conquer',
            'Next Big Icon: Rise to the Top!',
            'Battle of the Best: Who Will Reign?',
            'The Ultimate Face-Off: Prove Your Worth!',
            'Legends in the Making: Compete for Glory',
            'The Spotlight Challenge: Shine Bright!',
            'Victory Quest: Only One Can Win!',
        ];

        $predefinedRequirements = [
            // Text requirements
            ['type' => 'text', 'name' => 'Your Full Name', 'description' => 'Enter your full legal name as it appears on your ID.'],
            ['type' => 'text', 'name' => 'Food Name', 'description' => 'Provide the official name of the Food  you are entering.'],
            ['type' => 'text', 'name' => 'Email Address', 'description' => 'Enter a valid email address for communication.'],
            ['type' => 'text', 'name' => 'Phone Number', 'description' => 'Provide an active phone number for verification.'],

            // Image requirements
            ['type' => 'image', 'name' => 'Profile Picture', 'description' => 'Upload a clear and recent profile picture of yourself.'],
            ['type' => 'image', 'name' => 'ID or Passport', 'description' => 'Upload a scanned copy of your identification document.'],
            ['type' => 'image', 'name' => 'Contest Entry Artwork', 'description' => 'Submit an image of your contest entry, if applicable.'],
            ['type' => 'image', 'name' => 'Receipt of Payment', 'description' => 'Upload proof of payment for contest entry fees, if required.'],

            // Video requirements
            ['type' => 'video', 'name' => 'Introduction Video', 'description' => 'Record a short video introducing yourself and your entry.'],
            ['type' => 'video', 'name' => 'Talent Showcase', 'description' => 'Submit a video demonstrating your talent or performance.'],
            ['type' => 'video', 'name' => 'Testimonial or Motivation', 'description' => 'Share a short video explaining why you are participating.'],
            ['type' => 'video', 'name' => 'Final Submission', 'description' => 'Upload your final contest entry in video format.'],
        ];

        return [
            'organizer_id'      => User::where('role', 'organizer')->inRandomOrder()->first()->id,
            'category_id'       => Category::inRandomOrder()->first()->id,
            'title'             => fake()->randomElement($titles) . ' - ' . fake()->sentence(1),
            'description'       => fake()->paragraph(6),
            'slug'              => Str::slug(fake()->sentence(3)),
            'image'             => fake()->randomElement(['contests/images/1739764319-9zK2OVEqqNFeRLLx0onnp23PdF.jpg', 'contests/images/1738533106-L3YfEtoE4niWwL4p9PAUlJE2yQ.jpg', 'contests/default.jpg']),
            'type'              => fake()->randomElement(['free', 'paid', 'exclusive']),
            'amount'            => fake()->randomNumber(2, true),
            'entry_type'        => fake()->randomElement(['free', 'paid', 'exclusive']),
            'entry_fee'         => fake()->randomFloat(0, 10, 50),
            'entry_status'      => fake()->randomElement(['open']),
            'prize'             => fake()->numberBetween(10, 100),
            'status'            => 'active',
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'voting_start_date' => $votingStartDate,
            'voting_end_date'   => $votingEndDate,
            'max_entries'       => fake()->numberBetween(10, 500),
            'featured'          => fake()->boolean(60),
            'entry_coins'       => fake()->numberBetween(0, 10000),
            'voting_coins'      => fake()->numberBetween(0, 50000),
            'rules'             => fake()->paragraph(7),
            'requirements'      => collect($predefinedRequirements)->random(3)->values()->toArray(),
        ];
    }
}
