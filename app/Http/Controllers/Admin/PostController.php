<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('user', 'comments')->latest();

        if ($request->search) {
            $query->search($request->search);
        }
        $posts = $query->paginate(50);

        return view('admin.posts.index', compact('posts'));
    }

    public function viewPost(Request $request, $id)
    {
        $post = Post::whereId($id)->with('user', 'comments')->first();

        return view('admin.posts.view', compact('post'));
    }

    public function comments(Request $request)
    {
        $query = PostComment::latest();

        if ($request->search) {
            $query->searchComment($request->search);
        }
        $comments = $query->paginate(50);

        return view('admin.posts.comments', compact('comments'));
    }

    public function reports()
    {
        $reportedPosts = Post::with('user')->get();

        return view('admin.posts.reports');
    }

    public function settings()
    {
        return view('admin.posts.settings');
    }

    public function updatePost(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'content' => 'nullable|string',
        ]);

        $post->update($request->only('content'));

        return response()->json(['status' => 'success', 'message' => 'Post updated successfully', 'url' => route('admin.community.view', $post->id)], 200);
    }

    public function deletePost($id)
    {
        $post = Post::findOrFail($id);

        // Delete associated comments and likes
        $post->comments()->delete();
        $post->likes()->delete();

        $post->delete();

        return to_route('admin.community.posts')->withSuccess('Post deleted successfully');
    }

    // update comment
    public function updateComment(Request $request, $id)
    {
        $comment = PostComment::findOrFail($id);

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Comment updated successfully'], 200);
    }

    public function deleteComment($id)
    {
        $comment = PostComment::findOrFail($id);
        $comment->delete();

        return response()->json(['status' => 'success', 'message' => 'Comment deleted successfully'], 200);
    }
}
