<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleShare extends Model
{
    protected $fillable = ['user_id', 'article_id'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
