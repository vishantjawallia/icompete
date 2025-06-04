<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Traits\ApiResponse;
use App\Traits\PostTrait;
use Auth;
use Illuminate\Http\Request;
use Purify;

class CommunityController extends Controller
{
    use ApiResponse, PostTrait;

    public function posts(Request $request)
    {
        $pp = $request->count ?? 20;
        $page = $request->input('page', 1);
        $query = Post::whereStatus('active')->orderByDesc('id');

        if ($request->search) {
            $query->searchPost($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->postObject($item),
            $pp,
            true
        );

        return $this->paginatedResponse('Posts retrieved', $objectData, $result);
    }

    /**
     * Retrieve a list of the currently authenticated user's posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myPosts(Request $request)
    {
        $pp = $request->count ?? 20;
        $page = $request->input('page', 1);
        $user = Auth::user();
        $query = Post::whereUserId($user->id)->orderByDesc('id');

        if ($request->search) {
            $query->searchPost($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->postObject($item),
            $pp
        );

        return $this->paginatedResponse('User Posts Fetched', $objectData, $result);

    }

    public function showPost($id, Request $request)
    {
        $post = Post::find($id);

        if (! $post) {
            return $this->errorResponse('Post not found.', 404);
        }

        return $this->successResponse('Post retrieved', $this->postObject($post));
    }

    // Create a new post
    public function createPost(Request $request)
    {
        $min = sys_setting('min_post_length');
        $max = sys_setting('max_post_length');
        $validated = $request->validate([
            'content'    => "required|string|min:$min|max:$max",
            'title'      => 'nullable|string|max:105',
            'image'      => 'nullable|string',
            'contest_id' => 'string|nullable|exists:contests,id',
            'type'       => 'required|in:normal,contest',
        ]);
        $req = Purify::clean($validated);
        $image = null;

        if ($request->image != null) {
            $image = $this->moveImage('contests', $request->image);
        }

        if ($request->type == 'contest' && isset($request->contest_id)) {
            $contest = Contest::find($request->contest_id);

            if (! $contest) {
                return $this->notFoundResponse('Contest not found');
            }
            $req['contest_id'] = $contest->id;
        }
        $status = 'pending';

        // check if post status is auto approve
        if (sys_setting('post_approval') == 1) {
            $status = 'active';
        }
        $user = Auth::user();
        $req['user_id'] = $user->id;
        $req['image'] = $image;
        $req['status'] = $status;
        $post = Post::create($req);

        return $this->successResponse('Post created', $this->postObject($post));
    }

    // Update an existing post
    public function updatePost(Request $request, $id)
    {
        $post = Post::find($id);

        if (! $post) {
            return $this->errorResponse('Post not found.', 404);
        }

        // Check if the current user is the owner of the post
        if ($post->user_id !== Auth::id()) {
            return $this->errorResponse('You are not authorized to update this post.', 403);
        }

        $min = sys_setting('min_post_length');
        $max = sys_setting('max_post_length');
        $validated = $request->validate([
            'content' => "required|string|min:$min|max:$max",
            'content' => 'required|string',
            'title'   => 'nullable|string|max:255',
            'image'   => 'nullable|string',
        ]);
        $req = Purify::clean($validated);
        $image = $post->image;

        if ($request->image != null) {
            $image = $this->moveImage('contests', $request->image);
        }

        $post->update([
            'content' => $req['content'],
            // 'title' => $req['title'],
            'image' => $image,
        ]);

        return $this->successResponse('Post updated', $this->postObject($post));
    }

    // Delete a post
    public function deletePost($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return $this->errorResponse('Post not found.', 404);
        }

        // Check if the current user is the owner of the post
        if ($post->user_id !== Auth::id()) {
            return $this->errorResponse('You are not authorized to delete this post.', 403);
        }

        $post->delete();

        return $this->successResponse('Post deleted successfully.');
    }

    /**
     * Like or unlike a post.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleLike($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return $this->errorResponse('Post not found.', 404);
        }
        $user = Auth::user();

        // Check if the user has already liked the post
        $like = PostLike::where('user_id', $user->id)->where('post_id', $post->id)->first();

        if ($like) {
            // Unlike the post if already liked
            $like->delete();
            $message = 'Post unliked successfully';
        } else {
            // Like the post if not liked
            PostLike::create(['user_id' => $user->id, 'post_id' => $post->id]);
            $message = 'Post liked successfully';
        }

        // Return the post with the updated like status
        return response()->json([
            'status'      => 'success',
            'message'     => $message,
            'likes_count' => $post->likes()->count(),
        ]);
    }

    // Make a comment on a post
    public function makeComment(Request $request, $id)
    {
        $min = sys_setting('min_comment_length');
        $max = sys_setting('max_comment_length');
        $request->validate([
            'content' => "required|string|min:$min|max:$max",
        ]);
        // purify request
        $req = Purify::clean($request->all());
        $post = Post::find($id);

        if (! $post) {
            return $this->errorResponse('Post not found.', 404);
        }
        $status = 'pending';

        // check if post status is auto approve
        if (sys_setting('comment_approval') == 1) {
            $status = 'active';
        }
        $user = Auth::user();
        $comment = PostComment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => $req['content'],
        ]);
        // send notification
        sendNotification('POST_COMMENT', $post->user, [
            'username'        => $post->user->username,
            'commenter'       => $user->username,
            'comment_preview' => textTrim($req['content'], 70),
        ], [
            'user_id'    => $user->id,
            'post_id'    => $post->id,
            'comment_id' => $comment->id,
            'type'       => 'COMMENT',
        ]);

        return $this->successResponse('Comment added successfully.', $this->commentObject($comment));
    }

    // List all comments for a post
    public function comment(Request $request, $id)
    {
        $pp = $request->count ?? 30;
        $page = $request->input('page', 1);

        $post = Post::find($id);

        if (! $post) {
            return $this->notFoundResponse('Post not found.');
        }
        $query = PostComment::wherePostId($post->id)->orderByDesc('id');

        if ($request->search) {
            $query->searchComment($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->commentObject($item),
            $pp,
            true
        );

        return $this->paginatedResponse('Comments retrieved successfully.', $objectData, $result);
    }

    // Update a comment
    public function updateComment(Request $request, $id)
    {
        $comment = PostComment::findOrFail($id);

        // Check if the current user is the owner of the comment
        if ($comment->user_id !== Auth::id()) {
            return $this->errorResponse('You are not authorized to update this comment.', 403);
        }

        $min = sys_setting('min_comment_length');
        $max = sys_setting('max_comment_length');
        $request->validate([
            'content' => "required|string|min:$min|max:$max",
        ]);
        $req = Purify::clean($request->all());

        $comment->update([
            'content' => $req['content'],
        ]);

        return $this->successResponse('Comment updated successfully', $this->commentObject($comment));
    }

    // Delete a comment if the user is authorized (either the comment owner or the contest organizer)
    public function deleteComment($id)
    {
        $comment = PostComment::findOrFail($id);

        $post = $comment->post;

        // Organizer can delete contests post comments
        if ($post->type == 'contest') {
            if ($post->user_id !== Auth::id() && $comment->user_id !== Auth::id()) {
                return $this->errorResponse('You are not authorized to delete this comment.', 403);
            }
        } else {
            if ($comment->user_id !== Auth::id()) {
                return $this->errorResponse('You are not authorized to delete this comment.', 403);
            }
        }

        $comment->delete();

        return $this->successResponse('Comment deleted.');
    }
}
