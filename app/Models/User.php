<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\LegacyPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'UserName',
        'Password',
        'Role',
    ];

    protected $hidden = [
        'Password',
    ];

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function getRoleAttribute()
    {
        return $this->attributes['Role'] ?? $this->attributes['role'] ?? null;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['Password'] = LegacyPassword::make($value);
    }
}
