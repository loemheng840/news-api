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
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
        }

        $article = Article::create([
            'title'       => $request->title,
            'slug'        => Str::slug($request->title),
            'content'     => $request->content,
            'thumbnail'   => $path,
            'author_id'   => $request->user()->id,
            'status'      => 'DRAFT',
            'category_id' => $request->category_id,
        ]);

        return response()->json($article, 201);
    }

    // All Published Articles
    public function index()
    {
        return Article::with(['author','category','tags','likes'])
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
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
            ->where('author_id',$request->user()->id)
            ->latest()
            ->get();
    }
}
