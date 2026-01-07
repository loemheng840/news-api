<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleView extends Model
{
    protected $fillable = ['article_id','ip_address'];

    public $timestamps = false;

    public function article(){
        return $this->belongTo(Article::class);
    }
}
