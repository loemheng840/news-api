<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Get comments of an article (only approved, with replies)
     * GET /api/articles/{article}/comments
     */
    public function index($article)
    {
        return Comment::where('article_id', $article)
            ->whereNull('parent_id')
            ->where('status', 'APPROVED')
            ->with(['replies.user', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get replies of a specific parent comment inside an article
     * GET /api/articles/{article}/comments/{parent}/replies
     */
    public function replies(Comment $comment)
    {
        return Comment::where('parent_id', $comment->id)
            ->where('status', 'APPROVED')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Create new comment or reply
     * POST /api/comments
     */
    public function store(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content'    => 'required|string|min:1|max:1000',
            'parent_id'  => 'nullable|exists:comments,id',
        ]);

        // If parent_id is provided, validate it belongs to the same article
        if ($request->parent_id) {
            $parentComment = Comment::find($request->parent_id);
            if ($parentComment->article_id != $request->article_id) {
                return response()->json([
                    'message' => 'Parent comment must belong to the same article'
                ], 422);
            }
        }

        $comment = Comment::create([
            'article_id' => $request->article_id,
            'user_id'    => $request->user()->id,
            'parent_id'  => $request->parent_id,
            'content'    => $request->content,
            'status'     => 'APPROVED',
            'ip_address' => $request->ip(),
        ]);

        // Load user relationship for response
        $comment->load('user');

        // Send notifications
        app(NotificationService::class)->notifyCommentOwner($comment);

        return response()->json($comment, 201);
    }

    /**
     * Update own comment
     * PUT/PATCH /api/comments/{comment}
     */
    public function update(Request $request, Comment $comment)
    {
        abort_if($comment->user_id !== $request->user()->id, 403);

        $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json($comment);
    }

    /**
     * Delete (owner or admin)
     * DELETE /api/comments/{comment}
     */
    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'ADMIN') {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Admin approve / reject
     * POST /api/comments/{comment}/moderate
     */
    public function moderate(Request $request, Comment $comment)
    {
        abort_if($request->user()->role !== 'ADMIN', 403);

        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED,PENDING',
        ]);

        $comment->update([
            'status' => $request->status,
        ]);

        // For admin, expose ip_address
        $comment->makeVisible('ip_address');

        return response()->json([
            'message' => 'Comment status updated',
            'comment' => $comment
        ]);
    }

    /**
     * Admin: list comments with optional status filter.
     * GET /api/admin/comments
     */
    public function adminIndex(Request $request)
    {
        $query = Comment::with(['user', 'article'])->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            if (!in_array($request->status, ['PENDING', 'APPROVED', 'REJECTED'])) {
                return response()->json(['message' => 'Invalid status value'], 422);
            }
            $query->where('status', $request->status);
        }

        $comments = $query->paginate(20);

        // Expose ip_address for admin
        $comments->getCollection()->transform(function ($comment) {
            $comment->makeVisible('ip_address');
            return $comment;
        });

        return response()->json($comments);
    }
}
