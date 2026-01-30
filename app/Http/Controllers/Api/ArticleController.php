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
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $article = Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'thumbnail' => $request->hasFile('thumbnail') ? $request->file('thumbnail')->store('thumbnails','public') : null,
            'category_id' => $request->category_id,
            'author_id' => $request->user()->id,
            'status' => 'PUBLISHED',
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
}