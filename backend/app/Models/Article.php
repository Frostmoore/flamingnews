<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'description',
        'content',
        'url',
        'url_to_image',
        'source_name',
        'source_domain',
        'author',
        'published_at',
        'category',
        'topic_id',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_domain', 'domain');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'user_reads', 'article_id', 'user_id')
            ->withPivot('read_at');
    }

    public function likes()
    {
        return $this->hasMany(ArticleLike::class);
    }

    /**
     * Returns first 3 sentences of content for AI analysis input.
     */
    public function getExcerptAttribute(): string
    {
        $text = $this->description ?? $this->content ?? '';
        $sentences = preg_split('/(?<=[.!?])\s+/', strip_tags($text), 4);
        return implode(' ', array_slice($sentences, 0, 3));
    }
}
