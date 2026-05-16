<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Notifications\NewArticleNotification;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewArticleNotifications implements ShouldQueue
{
    public function handle(ArticlePublished $event): void
    {
        $article = $event->article;
        $author = $article->author;

        // Send push/email notifications via Laravel's notification system
        $author->followers()->chunk(100, function ($followers) use ($article) {
            foreach ($followers as $follower) {
                $follower->notify(new NewArticleNotification($article));
            }
        });

        // Create in-app notifications via NotificationService
        app(NotificationService::class)->notifyFollowers($article);

        $article->update(['notified_at' => now()]);
    }
}
