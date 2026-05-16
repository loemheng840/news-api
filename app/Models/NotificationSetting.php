<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'follow_notifications',
        'comment_notifications',
        'like_notifications',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'follow_notifications' => 'boolean',
        'comment_notifications' => 'boolean',
        'like_notifications' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
