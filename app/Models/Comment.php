<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Comment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'ip_address',
    ];

    protected $hidden = [
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('status', 'APPROVED')
            ->orderBy('created_at', 'asc');
    }

    public function allReplies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function isReply()
    {
        return !is_null($this->parent_id);
    }

    public function isTopLevel()
    {
        return is_null($this->parent_id);
    }
}
