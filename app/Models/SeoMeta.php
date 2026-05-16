<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'article_id',
        'meta_title',
        'meta_description',
        'og_image',
        'canonical_url',
        'schema_json',
    ];

    protected $casts = [
        'schema_json' => 'array',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
