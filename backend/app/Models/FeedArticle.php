<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedArticle extends Model
{
    protected $fillable = [
        'feed_url', 'title', 'description', 'url', 'url_to_image', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
