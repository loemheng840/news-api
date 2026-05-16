<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Article extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'title', 'slug', 'content', 'thumbnail', 'status',
        'category_id', 'author_id', 'published_at', 'notified_at',
        'excerpt', 'is_featured', 'is_breaking', 'reading_time_minutes',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_breaking' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function articleViews()
    {
        return $this->hasMany(ArticleView::class);
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function seoMeta()
    {
        return $this->hasOne(SeoMeta::class);
    }
}
