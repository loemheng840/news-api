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
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\ArticleRevisionController;
use App\Http\Controllers\Api\SeoMetaController;
use App\Http\Controllers\Api\MediaLibraryController;
use App\Http\Controllers\Api\SearchLogController;
use App\Http\Controllers\Api\AdPlacementController;
use App\Http\Controllers\Api\AdImpressionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationSettingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisteredUserController::class, 'stored']);


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Auth Required)
|--------------------------------------------------------------------------
*/

// Active ad placements
Route::get('/ads/active', [AdPlacementController::class, 'active']);

// Public user profile
Route::get('/users/{id}/profile', [UserProfileController::class, 'showPublic']);

// Ad impressions (guest-accessible)
Route::post('/ad-impressions', [AdImpressionController::class, 'store']);
Route::patch('/ad-impressions/{id}/click', [AdImpressionController::class, 'click']);

// Article view (guest-accessible)
Route::post('/articles/{id}/view', [EngagementController::class, 'view']);


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
    | USER PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::put('/profile', [UserProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::get('/notification-settings', [NotificationSettingController::class, 'show']);
    Route::put('/notification-settings', [NotificationSettingController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */
    Route::post('/reports', [ReportController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | MEDIA LIBRARY
    |--------------------------------------------------------------------------
    */
    Route::post('/media', [MediaLibraryController::class, 'store']);
    Route::get('/media', [MediaLibraryController::class, 'index']);
    Route::delete('/media/{id}', [MediaLibraryController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | ARTICLES (AUTHOR / EDITOR)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:AUTHOR,ADMIN')->group(function () {
        Route::get('/editor/articles', [ArticleController::class, 'myArticles']);

        Route::post('/articles', [ArticleController::class, 'store']);
        Route::put('/articles/{article}', [ArticleController::class, 'update']);
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

        Route::post('/articles/{article}/submit', [ArticleController::class, 'submit']);
        Route::post('/articles/{article}/meta', [ArticleController::class, 'attachMeta']);
        Route::get('/articles/me', [ArticleController::class, 'myArticles']);

        Route::post('/articles/{article}/like', [EngagementController::class, 'like']);
        Route::delete('/articles/{article}/like', [EngagementController::class, 'unlike']);

        // Article Revisions
        Route::get('/articles/{article}/revisions', [ArticleRevisionController::class, 'index']);
        Route::get('/articles/{article}/revisions/{version}', [ArticleRevisionController::class, 'show']);

        // SEO Meta
        Route::post('/articles/{article}/seo', [SeoMetaController::class, 'store']);
        Route::put('/articles/{article}/seo', [SeoMetaController::class, 'update']);
        Route::get('/articles/{article}/seo', [SeoMetaController::class, 'show']);
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
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);
    Route::delete('/comments/{comment}/like', [CommentController::class, 'unlike']);
    Route::get('/comments/{comment}/replies', [CommentController::class, 'replies']);

    Route::middleware('role:ADMIN')->group(function () {
        Route::patch('/comments/{comment}/moderate', [CommentController::class, 'moderate']);
        Route::get('/admin/comments', [CommentController::class, 'adminIndex']);
    });

    /*
    |--------------------------------------------------------------------------
    | ENGAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::post('/articles/{article}/bookmark', [EngagementController::class, 'bookmark']);
    Route::delete('/articles/{article}/bookmark', [EngagementController::class, 'unbookmark']);

    /*
    |--------------------------------------------------------------------------
    | View & Bookmarks
    |--------------------------------------------------------------------------
    */
    Route::get('/articles/admin', [ArticleController::class, 'index']);
    Route::get('/me/bookmarks', [EngagementController::class, 'myBookmarks']);

    /*
    |--------------------------------------------------------------------------
    | FOLLOW SYSTEM
    |--------------------------------------------------------------------------
    */
    Route::post('/authors/{author}/follow', [FollowController::class, 'follow']);
    Route::delete('/authors/{author}/follow', [FollowController::class, 'unfollow']);
    Route::get('/me/following', [FollowController::class, 'following']);
    Route::get('/me/followers', [FollowController::class, 'followers']);
    Route::get('/authors/{author}/follow-status', [FollowController::class, 'checkStatus']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:ADMIN')->group(function () {
        // Article restore
        Route::post('/articles/{article}/restore', [ArticleController::class, 'restore']);

        // Search logs
        Route::get('/admin/search-logs', [SearchLogController::class, 'index']);

        // Reports management
        Route::get('/admin/reports', [ReportController::class, 'index']);
        Route::patch('/admin/reports/{id}', [ReportController::class, 'review']);

        // Audit logs
        Route::get('/admin/audit-logs', [AuditLogController::class, 'index']);

        // Ad analytics
        Route::get('/admin/ad-analytics', [AdImpressionController::class, 'analytics']);

        // Article view analytics
        Route::get('/admin/article-analytics', [EngagementController::class, 'analytics']);

        // Ad placements CRUD
        Route::get('/ad-placements', [AdPlacementController::class, 'index']);
        Route::post('/ad-placements', [AdPlacementController::class, 'store']);
        Route::get('/ad-placements/{id}', [AdPlacementController::class, 'show']);
        Route::put('/ad-placements/{id}', [AdPlacementController::class, 'update']);
        Route::delete('/ad-placements/{id}', [AdPlacementController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
| Static → Dynamic
*/
Route::get('/articles/{article}/comments', [CommentController::class, 'index']);
Route::get('/articles/{article}/comments/{parent}/replies', [CommentController::class, 'replies']);

// Article discovery
Route::get('/articles/latest', [ArticleController::class, 'latest']);
Route::get('/articles/trending', [ArticleController::class, 'trending']);
Route::get('/articles/featured', [ArticleController::class, 'featured']);
Route::get('/articles/search', [ArticleController::class, 'search']);
Route::get('/articles/category/{slug}', [ArticleController::class, 'byCategory']);
Route::get('/articles/tag/{slug}', [ArticleController::class, 'byTag']);
Route::get('/articles/date', [ArticleController::class, 'byDate']);
Route::get('/articles/{article}/related', [ArticleController::class, 'related']);

// Public reads
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/categories/{slug}/articles', [CategoryController::class, 'articles']);
Route::get('/tags', [TagController::class, 'index']);

// Article detail (slug-based)
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

// Articles by Tag
Route::get('/tags/{slug}/articles', [ArticleController::class, 'byTag']);
Route::get('/articles', [ArticleController::class, 'index']);
