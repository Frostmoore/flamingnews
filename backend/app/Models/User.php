<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'is_premium',
        'preferred_categories',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'is_premium'            => 'boolean',
            'preferred_categories'  => 'array',
        ];
    }

    public function reads()
    {
        return $this->hasMany(UserRead::class);
    }

    public function readArticles()
    {
        return $this->belongsToMany(Article::class, 'user_reads', 'user_id', 'article_id')
            ->withPivot('read_at');
    }
}
