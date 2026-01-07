<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ArticleEngaged implements ShouldBroadcast
{
    use SerializesModels;

    public $articleId;
    public $likesCount;

    public function __construct($articleId, $likesCount)
    {
        $this->articleId = $articleId;
        $this->likesCount = $likesCount;
    }

    public function broadcastOn()
    {
        return new Channel('article.' . $this->articleId);
    }
}
