<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    // Create Tag
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:tags,name|max:50'
        ]);

        return Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);
    }
    // Get all tags
    public function index()
    {
        return Tag::orderBy('name')->get();
    }
}
