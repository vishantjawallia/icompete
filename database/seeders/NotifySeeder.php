<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class NotifySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::inRandomOrder()->first();

        // Create 10 sample posts
        \App\Models\Post::factory(40)->create([
            'user_id' => $user->id,
        ]);
    }
}
