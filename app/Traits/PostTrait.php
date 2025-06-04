<?php

namespace App\Traits;

use Str;

trait PostTrait
{
    /**
     * Get optional authenticated user
     */
    protected function getOptionalUser()
    {
        // Check if we have a valid token without throwing an error
        if (request()->bearerToken()) {
            return \Auth::guard('sanctum')->user();
        }

    }

    public function postObject($post)
    {
        $user = $this->getOptionalUser();
        $userId = $user->id ?? null;
        $data = [
            'id'             => $post->id,
            'user_id'        => $post->user_id,
            'contest_id'     => $post->contest_id,
            'title'          => $post->title,
            'content'        => $post->content,
            'image'          => ($post->image) ? my_asset($post->image) : null,
            'type'           => $post->type,
            'status'         => $post->status,
            'liked_by_user'  => $post->likes()->where('user_id', $userId)->exists(),
            'likes_count'    => $post->likes()->count(),
            'comments_count' => $post->comments()->count(),
            'created_at'     => $post->created_at,
        ];
        $data['like_status'] = $this->postLikeStatus($post, $user);

        if ($post->user) {
            $data['user'] = [
                'id'       => $post->user_id,
                'username' => $post->user->username,
                'name'     => $post->user->fullname,
                'image'    => ($post->user->image) ? my_asset($post->user->image) : my_asset('users/default.jpg'),
            ];
        }

        if ($post->contest) {
            $contest = $post->contest;
            $data['contest'] = [
                'title'      => $contest->title,
                'image'      => ($contest->image) ? my_asset($contest->image) : my_asset('contests/default.jpg'),
                'type'       => $contest->type,
                'amount'     => ($contest->amount),
                'slug'       => $contest->slug,
                'category'   => $contest->category,
                'entry_type' => $contest->entry_type,
                'entry_fee'  => $contest->entry_fee,
            ];
        }

        return $data;
    }

    // comment object
    public function commentObject($comment)
    {
        // Prepare the comment data
        $commentData = [
            'id'         => $comment->id,
            'post_id'    => $comment->post_id,
            'user_id'    => $comment->user_id,
            'content'    => $comment->content,
            'created_at' => $comment->created_at,
        ];

        if ($comment->post) {
            $commentData['post'] = [
                'id'             => $comment->post->id,
                'title'          => $comment->post->title,
                'content'        => $comment->post->content,
                'image'          => $comment->post->image,
                'likes_count'    => $comment->post->likes()->count(),
                'comments_count' => $comment->post->comments()->count(),
            ];
        }

        // Only include user if it exists
        if ($comment->user) {
            $commentData['user'] = [
                'id'       => $comment->user->id,
                'username' => $comment->user->username,
                'name'     => $comment->user->fullname,
                'image'    => ($comment->user->image) ? my_asset($comment->user->image) : my_asset('users/default.jpg'),
            ];
        }

        return $commentData;
    }

    // post like status
    public function postLikeStatus($post, $user)
    {
        $id = $user->id ?? null;

        if ($post->likes()->where('user_id', $id)->first()) {
            return 1;
        }

        return 0;
    }

    // upload image
    public function moveImage($folder, $filePath)
    {
        // Define the source file path
        $tempFile = public_path("temp/{$filePath}");

        // Check if the file exists in the temp directory
        if (! file_exists($tempFile)) {
            return; // Return null if the file doesn't exist
        }

        // Generate a unique file name
        $extension = pathinfo($filePath, PATHINFO_EXTENSION); // Get the file extension
        $fileName = now()->timestamp . '-' . Str::random(26) . '.' . $extension;

        // Define the target directory and file path
        $targetDir = public_path("uploads/{$folder}/");
        $targetFile = "{$targetDir}{$fileName}";

        // Ensure the target directory exists, create it if not
        if (! file_exists($targetDir)) {
            mkdir($targetDir, 0777, true); // Recursive directory creation
        }

        // Move the file to the target directory
        if (rename($tempFile, $targetFile)) {
            // Return the relative file path for storage
            return "{$folder}/{$fileName}";
        }

        // If the move operation fails, return null

    }
}
