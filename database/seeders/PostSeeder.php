<?php

namespace Database\Seeders;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::inRandomOrder()->first();

        // Optionally, get a random contest (if you're associating posts with contests)
        $contest = Contest::inRandomOrder()->first();

        // Create 10 sample posts
        \App\Models\Post::factory(10)->create([
            'user_id' => $user->id,  // Assign random user to each post
        ]);
    }
}
