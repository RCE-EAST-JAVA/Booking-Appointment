<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'rce_db';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sync_bimbingan',
        'sso_token',
        'sso_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'sso_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sync_bimbingan' => 'boolean',
            'sso_token_expires_at' => 'datetime',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function dateOverrides(): HasMany
    {
        return $this->hasMany(DateOverride::class);
    }
}
