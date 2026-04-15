<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimaPagina extends Model
{
    protected $table = 'prima_pagine';

    protected $fillable = [
        'source_name', 'source_domain', 'political_lean',
        'image_url', 'headline', 'article_url',
        'edition_date', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'edition_date' => 'date',
            'fetched_at'   => 'datetime',
        ];
    }
}
