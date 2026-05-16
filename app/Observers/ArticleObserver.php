<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\ArticleRevision;

class ArticleObserver
{
    /**
     * Handle the Article "updating" event.
     * Create a revision when title or content changes.
     */
    public function updating(Article $article): void
    {
        if ($article->isDirty('title') || $article->isDirty('content')) {
            $latestVersion = ArticleRevision::where('article_id', $article->id)
                ->max('version') ?? 0;

            ArticleRevision::create([
                'article_id' => $article->id,
                'editor_id' => auth()->id() ?? $article->author_id,
                'title' => $article->getOriginal('title'),
                'content' => $article->getOriginal('content'),
                'change_note' => null,
                'version' => $latestVersion + 1,
            ]);
        }
    }

    /**
     * Handle the Article "saving" event.
     * Calculate reading_time_minutes from content word count.
     */
    public function saving(Article $article): void
    {
        if ($article->content) {
            $wordCount = str_word_count(strip_tags($article->content));
            $article->reading_time_minutes = max(1, (int) ceil($wordCount / 200));
        }
    }
}
