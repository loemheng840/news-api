<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewArticleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct(public Article $article)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $authorName = $this->article->author->name;
        $excerpt = Str::limit(strip_tags($this->article->content), 150);
        $url = config('app.url') . '/articles/' . $this->article->slug;

        return (new MailMessage)
            ->subject("New article from {$authorName}: {$this->article->title}")
            ->greeting('Hello!')
            ->line("{$authorName} just published a new article:")
            ->line("**{$this->article->title}**")
            ->line($excerpt)
            ->action('Read Article', $url)
            ->line("Thank you for following {$authorName}!");
    }
}
