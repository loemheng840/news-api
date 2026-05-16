<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;

class NotificationService
{
    /**
     * Notify all followers when an author publishes a new article.
     */
    public function notifyFollowers(Article $article): void
    {
        $followers = Follow::where('following_id', $article->author_id)->get();

        foreach ($followers as $follow) {
            if (!$this->shouldNotify($follow->follower_id, 'follow_notifications')) {
                continue;
            }

            Notification::create([
                'user_id' => $follow->follower_id,
                'type' => 'NEW_ARTICLE',
                'data' => [
                    'article_id' => $article->id,
                    'article_title' => $article->title,
                    'author_name' => $article->author->name ?? 'Unknown',
                ],
            ]);
        }
    }

    /**
     * Notify article author when a comment is posted.
     */
    public function notifyCommentOwner(Comment $comment): void
    {
        $article = $comment->article;

        // Notify article author (if commenter is not the author)
        if ($article && $article->author_id !== $comment->user_id) {
            if ($this->shouldNotify($article->author_id, 'comment_notifications')) {
                Notification::create([
                    'user_id' => $article->author_id,
                    'type' => 'COMMENT',
                    'data' => [
                        'article_id' => $article->id,
                        'article_title' => $article->title,
                        'commenter_name' => $comment->user->name ?? 'Unknown',
                        'comment_id' => $comment->id,
                    ],
                ]);
            }
        }

        // Notify parent comment owner if this is a reply
        if ($comment->parent_id) {
            $parentComment = Comment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_id !== $comment->user_id) {
                if ($this->shouldNotify($parentComment->user_id, 'comment_notifications')) {
                    Notification::create([
                        'user_id' => $parentComment->user_id,
                        'type' => 'COMMENT',
                        'data' => [
                            'article_id' => $article->id,
                            'commenter_name' => $comment->user->name ?? 'Unknown',
                            'comment_id' => $comment->id,
                        ],
                    ]);
                }
            }
        }
    }

    /**
     * Notify article author when their article is liked.
     */
    public function notifyArticleLiked(Like $like): void
    {
        $article = Article::find($like->article_id);
        if (!$article || $article->author_id === $like->user_id) {
            return;
        }

        if (!$this->shouldNotify($article->author_id, 'like_notifications')) {
            return;
        }

        $liker = User::find($like->user_id);

        Notification::create([
            'user_id' => $article->author_id,
            'type' => 'LIKE',
            'data' => [
                'article_id' => $article->id,
                'article_title' => $article->title,
                'liker_name' => $liker->name ?? 'Unknown',
            ],
        ]);
    }

    /**
     * Notify user when they gain a new follower.
     */
    public function notifyNewFollower(Follow $follow): void
    {
        if (!$this->shouldNotify($follow->following_id, 'follow_notifications')) {
            return;
        }

        $follower = User::find($follow->follower_id);

        Notification::create([
            'user_id' => $follow->following_id,
            'type' => 'FOLLOW',
            'data' => [
                'follower_id' => $follow->follower_id,
                'follower_name' => $follower->name ?? 'Unknown',
            ],
        ]);
    }

    /**
     * Check if a user has a specific notification type enabled.
     */
    protected function shouldNotify(int $userId, string $settingKey): bool
    {
        $settings = NotificationSetting::where('user_id', $userId)->first();

        if (!$settings) {
            return true; // Default to true if no settings exist
        }

        return (bool) $settings->{$settingKey};
    }
}
