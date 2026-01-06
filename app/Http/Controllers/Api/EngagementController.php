<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\ArticleView;

class EngagementController extends Controller
{
    // 👍 Like article
    public function like(Request $request, $articleId)
    {
        Like::firstOrCreate([
            'article_id' => $articleId,
            'user_id' => $request->user()->id
        ]);

        return response()->json(['message'=>'Liked']);
    }

    // 👎 Unlike article
    public function unlike(Request $request, $articleId)
    {
        Like::where('article_id',$articleId)
            ->where('user_id',$request->user()->id)
            ->delete();

        return response()->json(['message'=>'Unliked']);
    }

    // ⭐ Bookmark
    public function bookmark(Request $request, $articleId)
    {
        Bookmark::firstOrCreate([
            'article_id'=>$articleId,
            'user_id'=>$request->user()->id
        ]);

        return response()->json(['message'=>'Bookmarked']);
    }

    // ❌ Remove bookmark
    public function unbookmark(Request $request, $articleId)
    {
        Bookmark::where('article_id',$articleId)
            ->where('user_id',$request->user()->id)
            ->delete();

        return response()->json(['message'=>'Removed bookmark']);
    }

    // 👀 Unique view
    public function view(Request $request, $articleId)
    {
        ArticleView::firstOrCreate([
            'article_id' => $articleId,
            'ip_address' => $request->ip()
        ]);

        return response()->json(['message'=>'View counted']);
    }
}