<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\NotificationSetting;
use App\Observers\ArticleObserver;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Register Article observer for reading time and revisions
        Article::observe(ArticleObserver::class);

        // Auto-create UserProfile and NotificationSetting on user creation
        User::created(function (User $user) {
            UserProfile::create(['user_id' => $user->id]);
            NotificationSetting::create(['user_id' => $user->id]);
        });
    }
}
