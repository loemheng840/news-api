<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleRevision;
use Illuminate\Http\Request;

class ArticleRevisionController extends Controller
{
    /**
     * List all revisions for an article.
     * GET /api/articles/{article}/revisions
     */
    public function index(Request $request, $articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        // Only article author or admin can view revisions
        if ($request->user()->role !== 'ADMIN' && $request->user()->id !== $article->author_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $revisions = ArticleRevision::where('article_id', $articleId)
            ->orderBy('version', 'desc')
            ->paginate(20);

        return response()->json($revisions);
    }

    /**
     * Get a specific revision by version number.
     * GET /api/articles/{article}/revisions/{version}
     */
    public function show(Request $request, $articleId, $version)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        // Only article author or admin can view revisions
        if ($request->user()->role !== 'ADMIN' && $request->user()->id !== $article->author_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $revision = ArticleRevision::where('article_id', $articleId)
            ->where('version', $version)
            ->first();

        if (!$revision) {
            return response()->json(['message' => 'Revision not found'], 404);
        }

        return response()->json($revision);
    }
}
