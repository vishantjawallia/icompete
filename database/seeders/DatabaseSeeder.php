<?php

namespace Database\Seeders;

use App\Models\CoinTransaction;
use App\Models\Contest;
use App\Models\Notify;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Submission;
use App\Models\User;
use App\Models\Withdrawal;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Post::factory(20)->create();  // Create 10 posts
        // PostComment::factory(30)->create();  // Create 5 comments
        // Notify::factory(30)->create();
        // CoinTransaction::factory(33)->create();
        // Contest::factory(33)->create();
        // Submission::factory(300)->create();

        // Create some users first
        User::factory(10)->create();
        
        // Then create withdrawals
        Withdrawal::factory(300)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
