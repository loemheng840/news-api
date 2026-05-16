<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SeoMeta;
use Illuminate\Http\Request;

class SeoMetaController extends Controller
{
    /**
     * Create SEO metadata for an article.
     * POST /api/articles/{article}/seo
     */
    public function store(Request $request, $articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        if ($request->user()->role !== 'ADMIN' && $request->user()->id !== $article->author_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|url|max:2048',
            'canonical_url' => 'nullable|url|max:2048',
            'schema_json' => 'nullable|json',
        ]);

        $data = $request->only(['meta_title', 'meta_description', 'og_image', 'canonical_url', 'schema_json']);
        $data['article_id'] = $articleId;

        if (isset($data['schema_json']) && is_string($data['schema_json'])) {
            $data['schema_json'] = json_decode($data['schema_json'], true);
        }

        $seoMeta = SeoMeta::updateOrCreate(
            ['article_id' => $articleId],
            $data
        );

        return response()->json($seoMeta, 201);
    }

    /**
     * Update SEO metadata for an article.
     * PUT /api/articles/{article}/seo
     */
    public function update(Request $request, $articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        if ($request->user()->role !== 'ADMIN' && $request->user()->id !== $article->author_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|url|max:2048',
            'canonical_url' => 'nullable|url|max:2048',
            'schema_json' => 'nullable|json',
        ]);

        $data = $request->only(['meta_title', 'meta_description', 'og_image', 'canonical_url', 'schema_json']);

        if (isset($data['schema_json']) && is_string($data['schema_json'])) {
            $data['schema_json'] = json_decode($data['schema_json'], true);
        }

        $seoMeta = SeoMeta::updateOrCreate(
            ['article_id' => $articleId],
            $data
        );

        return response()->json($seoMeta);
    }

    /**
     * Get SEO metadata for an article.
     * GET /api/articles/{article}/seo
     */
    public function show($articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $seoMeta = SeoMeta::where('article_id', $articleId)->first();

        if (!$seoMeta) {
            return response()->json(['message' => 'SEO metadata not found'], 404);
        }

        return response()->json($seoMeta);
    }
}
