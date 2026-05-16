<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order')->with(['children' => function ($q) {
                    $q->orderBy('sort_order');
                }]);
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    public function show($slug)
    {
        return Category::where('slug', $slug)->firstOrFail();
    }

    public function articles($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return Article::with(['author', 'category', 'tags', 'likes'])
            ->where('category_id', $category->id)
            ->where('status', 'PUBLISHED')
            ->latest()
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        // Prevent self-reference (not possible on create, but validate parent depth)
        if ($request->parent_id) {
            if (!$this->validateDepth($request->parent_id)) {
                return response()->json([
                    'message' => 'Maximum nesting depth of 3 levels exceeded'
                ], 422);
            }
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'sometimes|required',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        // Prevent self-reference
        if ($request->has('parent_id') && $request->parent_id == $category->id) {
            return response()->json([
                'message' => 'A category cannot be its own parent'
            ], 422);
        }

        // Prevent circular reference
        if ($request->has('parent_id') && $request->parent_id) {
            if ($this->wouldCreateCircular($category->id, $request->parent_id)) {
                return response()->json([
                    'message' => 'Circular reference detected'
                ], 422);
            }

            if (!$this->validateDepth($request->parent_id)) {
                return response()->json([
                    'message' => 'Maximum nesting depth of 3 levels exceeded'
                ], 422);
            }
        }

        $data = [];
        if ($request->has('name')) {
            $data['name'] = $request->name;
            $data['slug'] = Str::slug($request->name);
        }
        if ($request->has('parent_id')) {
            $data['parent_id'] = $request->parent_id;
        }
        if ($request->has('description')) {
            $data['description'] = $request->description;
        }
        if ($request->has('sort_order')) {
            $data['sort_order'] = $request->sort_order;
        }

        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        // Promote children to top-level
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);

        $category->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Check if setting parent_id would create a circular reference.
     */
    private function wouldCreateCircular(int $categoryId, int $parentId): bool
    {
        $current = Category::find($parentId);
        while ($current) {
            if ($current->id === $categoryId) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Validate that the parent is not already at max depth (3 levels).
     */
    private function validateDepth(int $parentId): bool
    {
        $depth = 1;
        $current = Category::find($parentId);
        while ($current && $current->parent_id) {
            $depth++;
            $current = $current->parent;
        }
        // Parent is at depth $depth, child would be at $depth + 1
        return ($depth + 1) <= 3;
    }
}
