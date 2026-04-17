<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedArticle extends Model
{
    protected $fillable = [
        'user_feed_id', 'user_id', 'title', 'description',
        'url', 'url_to_image', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(UserFeed::class, 'user_feed_id');
    }
}
