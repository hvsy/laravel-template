<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

class CustomPersonalAccessToken extends PersonalAccessToken{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'extra',
    ];
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'extra' => 'json',
    ];
}
