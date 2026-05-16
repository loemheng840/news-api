<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleView extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'referrer',
        'read_percent',
        'time_on_page',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
