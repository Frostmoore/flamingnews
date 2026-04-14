<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRead extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'article_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
