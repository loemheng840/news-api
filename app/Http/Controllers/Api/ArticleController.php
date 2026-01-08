<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle thumbnail upload
        $path = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
        }

        // Create article (DRAFT only)
        $article = Article::create([
            'title'       => $request->title,
            'slug'        => Str::slug($request->title),
            'content'     => $request->content,
            'thumbnail'   => $path,
            'author_id'   => $request->user()->id,
            'status'      => 'DRAFT',
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Article created as draft',
            'article' => $article
        ], 201);
    }


      // Get all articles (Admin / Editor use)
    public function index()
    {
        return Article::latest()
            ->paginate(10); // change to ->get() if you don't want pagination
    }

    // it easy way to get all articles
    // public function index()
    // {
    //     return Article::all();
    // }


    // Get Article by Slug
   // Get Article by Slug
    public function show($slug)
    {
        return Article::where('slug', $slug)
            ->with(['author', 'categories', 'tags'])
            ->withCount(['likes', 'bookmarks', 'views'])
            ->firstOrFail();
    }


    // Update Article
    public function update(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id,403);

        $article->update($request->only('title','content'));
        return $article;
    }

    // Delete Article
    public function destroy(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id,403);
        $article->delete();

        return response()->json(['message'=>'Deleted']);
    }

    // Submit for Approval
    public function submit(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id,403);
        $article->update(['status'=>'ACHIVED']);

        return response()->json(['message'=>'Submitted']);
    }

    // Attach Categories & Tags
    public function attachMeta(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id,403);

        $article->categories()->sync($request->category_ids);
        $article->tags()->sync($request->tag_ids);

        return response()->json(['message'=>'Attached']);
    }

    // Latest
    public function latest()
    {
        return Article::where('status','PUBLISHED')
            ->latest('published_at')
            ->paginate(10);
    }

    // Trending
    public function trending()
    {
        return Article::where('status','PUBLISHED')
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }

    // Featured
    public function featured()
    {
        return Article::where('status','PUBLISHED')
            ->where('is_featured',true)
            ->get();
    }

    // Search
    public function search(Request $request)
    {
        return Article::where('status','PUBLISHED')
            ->where('title','like',"%{$request->q}%")
            ->paginate(10);
    }

    // By Category
    public function byCategory($slug)
    {
        return Article::whereHas('categories',fn($q)=>$q->where('slug',$slug))
            ->where('status','PUBLISHED')
            ->paginate(10);
    }

    // By Tag
    public function byTag($slug)
    {
        return Article::whereHas('tags',fn($q)=>$q->where('slug',$slug))
            ->where('status','PUBLISHED')
            ->paginate(10);
    }

    // By Date
    public function byDate(Request $request)
    {
        return Article::whereBetween('published_at',[$request->from,$request->to])
            ->where('status','PUBLISHED')
            ->paginate(10);
    }

    // Related Articles
    public function related($id)
    {
        $article = Article::findOrFail($id);

        return Article::whereHas('categories',fn($q)=>$q->whereIn(
            'categories.id',$article->categories->pluck('id')
        ))
        ->where('id','!=',$id)
        ->limit(5)
        ->get();
    }

    // My Articles
    public function myArticles(Request $request)
    {
        return Article::where('author_id',$request->user()->id)->get();
    }
}