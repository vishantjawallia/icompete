<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();

        // For each post, create 3 to 5 comments
        foreach ($posts as $post) {
            // Generate 3 to 5 comments per post
            \App\Models\PostComment::factory(rand(3, 5))->create([
                'user_id' => User::inRandomOrder()->first()->id,  // Assign random user to comment
                'post_id' => $post->id,  // Associate the comment with the current post
            ]);
        }
    }
}
