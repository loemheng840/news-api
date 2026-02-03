<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'content',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who wrote the comment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the article this comment belongs to
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the parent comment (if this is a reply)
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('status', 'APPROVED')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all replies (including unapproved - for admin)
     */
    public function allReplies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Check if this comment is a reply
     */
    public function isReply()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if this comment is a top-level comment
     */
    public function isTopLevel()
    {
        return is_null($this->parent_id);
    }
}
