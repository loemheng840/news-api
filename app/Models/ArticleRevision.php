<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleRevision extends Model
{
    protected $fillable = [
        'article_id',
        'editor_id',
        'title',
        'content',
        'change_note',
        'version',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
