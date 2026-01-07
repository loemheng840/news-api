<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\ArticleView;
use Illuminate\Http\Request;
use App\Events\ArticleEngaged;

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

    /*
    |--------------------------------------------------------------------------
    | VIEW (UNIQUE BY IP)
    |--------------------------------------------------------------------------
    */
    public function view(Request $request, $articleId)
    {
        ArticleView::firstOrCreate([
            'article_id' => $articleId,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'View counted']);
    }
}
