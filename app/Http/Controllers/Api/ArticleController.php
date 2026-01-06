<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    // Editor: view own articles
    public function myArticles(Request $request)
    {
        return Article::where('author_id', $request->user()->id)->latest()->get();
    }

    // Create article (Draft by default)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'thumbnail' => 'image|mimes:jpg,png,jpeg|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
        }

        return Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'thumbnail' => $path,
            'author_id' => $request->user()->id,
            'status' => 'DRAFT'
        ]);
    }

    // Update own article
    public function update(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id, 403);

        $article->update($request->only('title', 'content'));
        return $article;
    }

    // Delete own article
    public function destroy(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id, 403);
        $article->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // Submit for approval
    public function submit(Article $article, Request $request)
    {
        abort_if($article->author_id !== $request->user()->id, 403);

        $article->update(['status' => 'PENDING']);
        return response()->json(['message' => 'Submitted for approval']);
    }

    public function latest()
    {
        return Article::where('status','PUBLISHED')
            ->latest('published_at')
            ->paginate(10);
    }

    public function trending()
    {
        return Article::where('status','PUBLISHED')
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }
    public function featured()
    {
        return Article::where('status','PUBLISHED')
            ->where('is_featured', true)
            ->latest()
            ->get();
    }
    public function show($slug)
    {
        return Article::where('slug',$slug)
            ->withCount(['likes','bookmarks'])
            ->with('author')
            ->firstOrFail();
    }
    public function related($id)
    {
        $article = Article::findOrFail($id);

        return Article::whereHas('categories', function ($q) use ($article) {
                $q->whereIn('categories.id', $article->categories->pluck('id'));
            })
            ->where('id','!=',$article->id)
            ->where('status','PUBLISHED')
            ->limit(5)
            ->get();
    }
    public function search(Request $request)
    {
        $q = $request->q;

        return Article::where('status','PUBLISHED')
            ->where(function($query) use ($q) {
                $query->where('title','like',"%$q%")
                    ->orWhere('content','like',"%$q%");
            })
            ->paginate(10);
    }

    public function byCategory($slug)
    {
        return Article::whereHas('categories', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
    }
    public function byTag($slug)
    {
        return Article::whereHas('tags', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
    }
    public function byDate(Request $request)
    {
        return Article::where('status','PUBLISHED')
            ->whereBetween('published_at', [$request->from, $request->to])
            ->latest()
            ->paginate(10);
    }

    public function attachMeta(Request $request, Article $article)
    {
        abort_if($article->author_id !== $request->user()->id, 403);

        $article->categories()->sync($request->category_ids);
        $article->tags()->sync($request->tag_ids);

        return response()->json(['message'=>'Categories & tags assigned']);
    }
}
