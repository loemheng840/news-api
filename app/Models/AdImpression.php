<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdImpression extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'placement_id',
        'article_id',
        'user_id',
        'clicked',
        'ip_address',
    ];

    protected $casts = [
        'clicked' => 'boolean',
        'created_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function placement()
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
