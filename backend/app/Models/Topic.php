<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'title',
        'keywords',
        'article_count',
        'ai_analysis',
        'ai_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'ai_generated_at' => 'datetime',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function articlesByLean()
    {
        return $this->articles()
            ->with('source')
            ->get()
            ->groupBy(fn ($a) => optional($a->source)->political_lean ?? 'international');
    }
}
