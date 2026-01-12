<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Article;   // 👈 IMPORTANT
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index() {
        return Category::all();
    }

   public function show($slug)
    {
        return Category::where('slug', $slug)->firstOrFail();
    }

    public function articles($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return Article::with(['author','category','tags','likes'])
            ->where('category_id', $category->id)
            ->where('status','PUBLISHED')
            ->latest()
            ->paginate(10);
    }
    public function store(Request $request) {
        $request->validate(['name'=>'required|unique:categories']);
        return Category::create([
            'name'=>$request->name,
            'slug'=>Str::slug($request->name)
        ]);
    }

    public function update(Request $request, Category $category) {
        $category->update([
            'name'=>$request->name,
            'slug'=>Str::slug($request->name)
        ]);
        return $category;
    }

    public function destroy(Category $category) {
        $category->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
