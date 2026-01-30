<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use App\Events\ArticleEngaged;
use App\Models\Article;

class EngagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIKE
    |--------------------------------------------------------------------------
    */

    public function like(Request $request, $articleId)
    {
        Like::firstOrCreate([
            'article_id' => $articleId,
            'user_id' => $request->user()->id
        ]);

        $likesCount = Like::where('article_id', $articleId)->count();

        broadcast(new ArticleEngaged($articleId, $likesCount));

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
    /*-------------------------------------------------------------------------
    |
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
    | VIEW (UNIQUE BY USERID)
    |--------------------------------------------------------------------------
    */
    public function view(Request $request, $articleId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $view = ArticleView::firstOrCreate([
            'article_id' => $articleId,
            'user_id' => $user->id
        ]);

        if ($view->wasRecentlyCreated) {
            Article::where('id', $articleId)->increment('views');
        }

        return response()->json(['message' => 'View counted']);
    }

}