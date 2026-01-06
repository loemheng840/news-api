<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    // Get comments of an article (nested)
    public function index($articleId)
    {
        return Comment::where('article_id', $articleId)
            ->whereNull('parent_id')
            ->where('status','APPROVED')
            ->with(['user','replies.user'])
            ->latest()
            ->get();
    }

    // Add comment / reply
    public function store(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        return Comment::create([
            'article_id' => $request->article_id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'status' => 'PENDING'
        ]);
    }

    // Edit own comment
    public function update(Request $request, Comment $comment)
    {
        abort_if(
            $comment->user_id !== $request->user()->id,
            403
        );

        $comment->update(['content' => $request->content]);

        return $comment;
    }

    // Delete comment (owner or admin)
    public function destroy(Request $request, Comment $comment)
    {
        if (
            $comment->user_id !== $request->user()->id &&
            $request->user()->role !== 'ADMIN'
        ) {
            abort(403);
        }

        $comment->delete();

        return response()->json(['message'=>'Deleted']);
    }

    // Admin moderation
    public function moderate(Request $request, Comment $comment)
    {
        abort_if($request->user()->role !== 'ADMIN', 403);

        $comment->update([
            'status' => $request->status
        ]);

        return response()->json(['message'=>'Comment updated']);
    }
}
