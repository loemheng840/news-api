<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index() {
        return Tag::all();
    }

    public function store(Request $request) {
        $request->validate(['name'=>'required|unique:tags']);

        return Tag::create([
            'name'=>$request->name,
            'slug'=>Str::slug($request->name)
        ]);
    }
}