<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeed extends Model
{
    protected $fillable = ['user_id', 'name', 'feed_url', 'last_fetched_at'];

    protected $casts = [
        'last_fetched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedArticles()
    {
        return FeedArticle::where('feed_url', $this->feed_url);
    }
}
