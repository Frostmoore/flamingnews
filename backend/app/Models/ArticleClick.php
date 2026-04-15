<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleClick extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'article_id', 'clicked_at'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
