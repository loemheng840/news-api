<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Get comments of an article (only approved, with replies)
   public function index($article)
    {
        return Comment::where('article_id',$article)
            ->whereNull('parent_id')
            ->where('status','APPROVED')
            ->with('replies.user')
            ->get();
    }

    // Create new comment
    public function store(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content'    => 'required|string',
            'parent_id'  => 'nullable|exists:comments,id',
        ]);

        return Comment::create([
            'article_id' => $request->article_id,
            'user_id'    => $request->user()->id,
            'parent_id'  => $request->parent_id,
            'content'    => $request->content,
            'status'     => 'APPROVED',
        ]);
    }

    // Update own comment
    public function update(Request $request, Comment $comment)
    {
        abort_if($comment->user_id !== $request->user()->id, 403);

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json($comment);
    }

    // Delete (owner or admin)
    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'ADMIN') {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // Admin approve / reject
    public function moderate(Request $request, Comment $comment)
    {
        abort_if($request->user()->role !== 'ADMIN', 403);

        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
        ]);

        $comment->update([
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
