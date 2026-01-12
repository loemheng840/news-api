<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($article)
    {
        return Comment::where('article_id',$article)
            ->whereNull('parent_id')
            ->where('status','APPROVED')
            ->with('replies.user')
            ->get();
    }

    public function store(Request $request)
    {
        return Comment::create([
            'article_id'=>$request->article_id,
            'user_id'=>$request->user()->id,
            'parent_id'=>$request->parent_id,
            'content'=>$request->content,
            'status'=>'PENDING'
        ]);
    }

    public function update(Request $request, Comment $comment)
    {
        abort_if($comment->user_id !== $request->user()->id,403);
        $comment->update(['content'=>$request->content]);
        return $comment;
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'ADMIN') {
            abort(403);
        }
        $comment->delete();
        return response()->json(['message'=>'Deleted']);
    }

    public function moderate(Request $request, Comment $comment)
    {
        abort_if($request->user()->role !== 'ADMIN',403);
        $comment->update(['status'=>$request->status]);
        return response()->json(['message'=>'Updated']);
    }
}
