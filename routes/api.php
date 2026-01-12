<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EngagementController;

/*
|--------------------------------------------------------------------------
| AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn (Request $request) => $request->user());

    /*
    |--------------------------------------------------------------------------
    | ARTICLES (AUTHOR / EDITOR)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:AUTHOR,EDITOR')->group(function () {
        Route::get('/editor/articles', [ArticleController::class, 'myArticles']);

        Route::post('/articles', [ArticleController::class, 'store']);
        Route::put('/articles/{article}', [ArticleController::class, 'update']);
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

        Route::post('/articles/{article}/submit', [ArticleController::class, 'submit']);
        Route::post('/articles/{article}/meta', [ArticleController::class, 'attachMeta']);


    });

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | USERS (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | TAGS (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/tags', [TagController::class, 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | COMMENTS (AUTH USER)
    |--------------------------------------------------------------------------
    */
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::middleware('role:ADMIN')->group(function () {
        Route::patch('/comments/{comment}/moderate', [CommentController::class, 'moderate']);
    });

    /*
    |--------------------------------------------------------------------------
    | ENGAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::post('/articles/{article}/like', [EngagementController::class, 'like']);
    Route::delete('/articles/{article}/like', [EngagementController::class, 'unlike']);

    Route::post('/articles/{article}/bookmark', [EngagementController::class, 'bookmark']);
    Route::delete('/articles/{article}/bookmark', [EngagementController::class, 'unbookmark']);
});

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
| Static → Dynamic
*/

// Article discovery
Route::get('/articles/latest', [ArticleController::class, 'latest']);
Route::get('/articles/trending', [ArticleController::class, 'trending']);
Route::get('/articles/featured', [ArticleController::class, 'featured']);
Route::get('/articles/search', [ArticleController::class, 'search']);
Route::get('/articles/category/{slug}', [ArticleController::class, 'byCategory']);
Route::get('/articles/tag/{slug}', [ArticleController::class, 'byTag']);
Route::get('/articles/date', [ArticleController::class, 'byDate']);
Route::get('/articles/{article}/related', [ArticleController::class, 'related']);

// Comments
Route::get('/articles/{article}/comments', [CommentController::class, 'index']);

// Views
Route::post('/articles/{article}/view', [EngagementController::class, 'view']);

// Public reads
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/categories/{slug}/articles', [CategoryController::class, 'articles']);
Route::get('/tags', [TagController::class, 'index']);

Route::get('/articles', [ArticleController::class, 'index']);

// Article detail (slug-based)
Route::get('/articles/{slug}', [ArticleController::class, 'show']);