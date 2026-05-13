<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // if you added roles
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Users this user follows (as a follower).
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'author_id')
                    ->withPivot('created_at');
    }

    /**
     * Users who follow this user (as an author).
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'author_id', 'follower_id')
                    ->withPivot('created_at');
    }
}
