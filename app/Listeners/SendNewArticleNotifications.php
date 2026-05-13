<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Notifications\NewArticleNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewArticleNotifications implements ShouldQueue
{
    public function handle(ArticlePublished $event): void
    {
        $article = $event->article;
        $author = $article->author;

        $author->followers()->chunk(100, function ($followers) use ($article) {
            foreach ($followers as $follower) {
                $follower->notify(new NewArticleNotification($article));
            }
        });

        $article->update(['notified_at' => now()]);
    }
}
