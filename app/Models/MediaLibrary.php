<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaLibrary extends Model
{
    protected $table = 'media_library';

    protected $fillable = [
        'uploader_id',
        'filename',
        'path',
        'mime_type',
        'file_size',
        'alt_text',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
