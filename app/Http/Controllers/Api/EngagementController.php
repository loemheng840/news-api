<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\ArticleView;
use App\Models\Article;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Events\ArticleEngaged;
use Illuminate\Support\Facades\DB;

class EngagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIKE
    |--------------------------------------------------------------------------
    */

    public function like(Request $request, $articleId)
    {
        $like = Like::firstOrCreate([
            'article_id' => $articleId,
            'user_id' => $request->user()->id
        ]);

        $likesCount = Like::where('article_id', $articleId)->count();

        broadcast(new ArticleEngaged($articleId, $likesCount));

        // Send notification if this is a new like
        if ($like->wasRecentlyCreated) {
            app(NotificationService::class)->notifyArticleLiked($like);
        }

        return response()->json(['message' => 'Liked']);
    }

    public function unlike(Request $request, $articleId)
    {
        Like::where('article_id', $articleId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Unliked']);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOKMARK
    |--------------------------------------------------------------------------
    */
    public function bookmark(Request $request, $articleId)
    {
        Bookmark::firstOrCreate([
            'article_id' => $articleId,
            'user_id'    => $request->user()->id,
        ]);

        return response()->json(['message' => 'Bookmarked']);
    }

    public function unbookmark(Request $request, $articleId)
    {
        Bookmark::where('article_id', $articleId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Removed']);
    }

    /*
    |--------------------------------------------------------------------------
    | GET MY BOOKMARK
    |--------------------------------------------------------------------------
    */
    public function myBookmarks(Request $request)
    {
        $articles = Bookmark::with('article')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($articles);
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW (Enhanced - supports guest and analytics data)
    |--------------------------------------------------------------------------
    */
    public function view(Request $request, $articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $request->validate([
            'read_percent' => 'nullable|integer|min:0|max:100',
            'time_on_page' => 'nullable|integer|min:0|max:86400',
            'session_id' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:2048',
        ]);

        $view = ArticleView::create([
            'article_id' => $articleId,
            'user_id' => $request->user()?->id,
            'session_id' => $request->input('session_id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->input('referrer'),
            'read_percent' => $request->input('read_percent'),
            'time_on_page' => $request->input('time_on_page'),
        ]);

        Article::where('id', $articleId)->increment('views');

        return response()->json(['message' => 'View recorded'], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN ANALYTICS
    |--------------------------------------------------------------------------
    */
    public function analytics(Request $request)
    {
        $stats = ArticleView::select(
            'article_id',
            DB::raw('COUNT(*) as total_views'),
            DB::raw('AVG(read_percent) as avg_read_percent'),
            DB::raw('AVG(time_on_page) as avg_time_on_page')
        )
        ->groupBy('article_id')
        ->paginate(20);

        return response()->json($stats);
    }
}
