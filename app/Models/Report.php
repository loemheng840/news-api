<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'reason',
        'status',
        'reviewed_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }
}
