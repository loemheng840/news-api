<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\EngagementController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', function () {
    return response()->json([
        'message' => 'API login route works'
    ]);
});


Route::post('/login', [AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn(Request $r) => $r->user());

    // Editor Articles (authenticated)
    Route::get('/editor/articles', [ArticleController::class, 'myArticles']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    Route::post('/articles/{article}/submit', [ArticleController::class, 'submit']);
    Route::post('/articles/{article}/meta', [ArticleController::class, 'attachMeta']);

    // Categories (authenticated)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Tags (authenticated)
    Route::post('/tags', [TagController::class, 'store']);

    // Comments (authenticated)
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::put('/comments/{comment}/moderate', [CommentController::class, 'moderate']);

    // Engagement (authenticated)
    Route::post('/articles/{article}/like', [EngagementController::class, 'like']);
    Route::delete('/articles/{article}/like', [EngagementController::class, 'unlike']);
    Route::post('/articles/{article}/bookmark', [EngagementController::class, 'bookmark']);
    Route::delete('/articles/{article}/bookmark', [EngagementController::class, 'unbookmark']);
});

// Public Routes (no authentication required)
// Specific routes MUST come before dynamic routes to avoid conflicts
Route::get('/articles/latest', [ArticleController::class, 'latest']);
Route::get('/articles/trending', [ArticleController::class, 'trending']);
Route::get('/articles/featured', [ArticleController::class, 'featured']);
Route::get('/articles/search', [ArticleController::class, 'search']);
Route::get('/articles/category/{slug}', [ArticleController::class, 'byCategory']);
Route::get('/articles/tag/{slug}', [ArticleController::class, 'byTag']);
Route::get('/articles/date', [ArticleController::class, 'byDate']);
Route::get('/articles/{article}/related', [ArticleController::class, 'related']);
Route::get('/articles/{article}/comments', [CommentController::class, 'index']);
Route::post('/articles/{article}/view', [EngagementController::class, 'view']);
Route::get('/articles/{article}', [ArticleController::class, 'show']); // Must be last

// Categories (public read)
Route::get('/categories', [CategoryController::class, 'index']);

// Tags (public read)
Route::get('/tags', [TagController::class, 'index']);
