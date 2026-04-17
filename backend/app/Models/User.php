<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'google_id',
        'avatar',
        'is_premium',
        'is_admin',
        'preferred_categories',
        'preferred_sources',
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
            'is_admin'              => 'boolean',
            'preferred_categories'  => 'array',
            'preferred_sources'     => 'array',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function reads()
    {
        return $this->hasMany(UserRead::class);
    }

    public function userFeeds()
    {
        return $this->hasMany(UserFeed::class);
    }

    public function readArticles()
    {
        return $this->belongsToMany(Article::class, 'user_reads', 'user_id', 'article_id')
            ->withPivot('read_at');
    }
}
