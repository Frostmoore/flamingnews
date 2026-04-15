<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'domain',
        'name',
        'political_lean',
        'country',
        'active',
        'tier',
        'feed_url',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'source_domain', 'domain');
    }

    public static function findByDomain(string $domain): ?self
    {
        return self::where('domain', $domain)->where('active', true)->first();
    }
}
