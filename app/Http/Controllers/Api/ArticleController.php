<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    // Create Article (Draft)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'nullable|in:DRAFT,PUBLISHED,ARCHIVED',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $status = $request->input('status', 'DRAFT');

        $article = Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'thumbnail' => $request->hasFile('thumbnail') ? $request->file('thumbnail')->store('thumbnails','public') : null,
            'category_id' => $request->category_id,
            'author_id' => $request->user()->id,
            'status' => $status,
            'published_at' => $status === 'PUBLISHED' ? now() : null,
        ]);

        // Attach tags
        if ($request->has('tag_ids')) {
            $article->tags()->sync($request->tag_ids);
        }

        return response()->json($article->load('tags'), 201);
    }


    // All Published Articles
    public function index(Request $request)
    {
        $query = Article::with(['author', 'category', 'tags', 'likes']);

        // If admin, show all (PUBLISHED + DRAFT)
        if ($request->user() && $request->user()->role === 'ADMIN') {
            return $query->orderBy('created_at', 'desc')->get();
        }

        // Public users only see PUBLISHED
        return $query->where('status', 'PUBLISHED')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    // Article Detail
    public function show($slug)
    {
        $article = Article::with(['author','category','tags','likes'])
            ->withCount(['likes','bookmarks'])
            ->where('slug',$slug)
            ->firstOrFail();
        return response()->json($article);
    }

    // Latest
    public function latest()
    {
        return Article::with(['author','category','tags','likes'])
            ->where('status','PUBLISHED')
            ->latest('created_at')
            ->paginate(10);
    }

    public function featured()
    {
        return Article::with(['author', 'category', 'tags', 'likes'])
            ->where('status', 'PUBLISHED')
            ->orderByDesc('views')
            ->latest('published_at')
            ->limit(5)
            ->get();
    }

    // Trending
    public function trending()
    {
        return Article::with(['author','category'])
            ->where('status','PUBLISHED')
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }

    // Search
    public function search(Request $request)
    {
        return Article::with(['author','category','tags','likes'])
            ->where('status','PUBLISHED')
            ->where('title','like',"%{$request->q}%")
            ->latest()
            ->paginate(10);
    }

    // Category Filter
    public function byCategory($slug)
    {
        $category = Category::where('slug',$slug)->firstOrFail();

        return Article::with(['author','category','tags','likes'])
            ->where('category_id',$category->id)
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
    }

    // Tag Filter
    public function byTag($slug)
    {
        return Article::with(['author','category','tags','likes'])
            ->whereHas('tags', fn($q)=>$q->where('slug',$slug))
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
    }

    // Date Range
    public function byDate(Request $request)
    {
        return Article::with(['author','category','tags','likes'])
            ->whereBetween('created_at',[$request->from,$request->to])
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
    }

    public function related($article)
    {
        $baseArticle = is_numeric($article)
            ? Article::with('tags')->findOrFail($article)
            : Article::with('tags')->where('slug', $article)->firstOrFail();

        $tagIds = $baseArticle->tags->pluck('id')->all();

        return Article::with(['author', 'category', 'tags', 'likes'])
            ->where('status', 'PUBLISHED')
            ->where('id', '!=', $baseArticle->id)
            ->where(function ($query) use ($baseArticle, $tagIds) {
                $query->where('category_id', $baseArticle->category_id);
                if (!empty($tagIds)) {
                    $query->orWhereHas('tags', fn ($tagQuery) => $tagQuery->whereIn('tags.id', $tagIds));
                }
            })
            ->latest()
            ->limit(6)
            ->get();
    }

    // My Articles
   public function myArticles(Request $request)
        {
            return Article::with(['category','tags'])
                ->withCount(['likes','bookmarks'])
                ->where('author_id',$request->user()->id)
                ->latest()
                ->get();
        }

    public function destroy(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        // Only author or admin can delete
        if ($request->user()->id !== $article->author_id && $request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $article->delete(); // call the method

        return response()->json([
            'message' => 'Article deleted successfully'
        ], 200);
    }


    // Update
    // Update Article
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        // Only author or admin can update
        if ($request->user()->id !== $article->author_id && $request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required',
            'category_id' => 'sometimes|exists:categories,id',
            'thumbnail' => 'nullable|image|max:2048',
            'status' => 'in:PUBLISHED,DRAFT',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        if ($request->has('title')) {
            $article->title = $request->title;
            $article->slug = Str::slug($request->title);
        }

        if ($request->has('content')) {
            $article->content = $request->content;
        }

        if ($request->has('category_id')) {
            $article->category_id = $request->category_id;
        }

        if ($request->hasFile('thumbnail')) {
            $article->thumbnail = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        if ($request->has('status')) {
            $article->status = $request->status;
            $article->published_at = $request->status === 'PUBLISHED'
                ? ($article->published_at ?? now())
                : null;
        }

        $article->save();

        // Sync tags
        if ($request->has('tag_ids')) {
            $article->tags()->sync($request->tag_ids);
        }

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $article->load(['author','category','tags'])
        ]);
    }

    public function submit(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        if ($request->user()->id !== $article->author_id && $request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $article->status = 'PUBLISHED';
        $article->published_at = $article->published_at ?? now();
        $article->save();

        return response()->json([
            'message' => 'Article submitted successfully',
            'article' => $article->load(['author', 'category', 'tags']),
        ]);
    }

    public function attachMeta(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        if ($request->user()->id !== $article->author_id && $request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'status' => 'nullable|in:DRAFT,PUBLISHED,ARCHIVED',
        ]);

        if ($request->filled('category_id')) {
            $article->category_id = $request->category_id;
        }

        if ($request->filled('status')) {
            $article->status = $request->status;
            $article->published_at = $request->status === 'PUBLISHED'
                ? ($article->published_at ?? now())
                : null;
        }

        $article->save();

        if ($request->has('tag_ids')) {
            $article->tags()->sync($request->tag_ids);
        }

        return response()->json([
            'message' => 'Article metadata updated successfully',
            'article' => $article->load(['author', 'category', 'tags']),
        ]);
    }
}
