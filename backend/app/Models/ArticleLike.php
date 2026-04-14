<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleLike extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'article_id', 'category'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
