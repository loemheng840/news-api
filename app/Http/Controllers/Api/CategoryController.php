<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index() {
        return Category::all();
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