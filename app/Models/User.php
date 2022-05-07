<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

/**
 * @method CustomPersonalAccessToken currentAccessToken()
 */
class User extends Authenticatable
{
    use HasApiTokens,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'account',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'account_verified_at' => 'datetime',
    ];

    public function createToken(string $name, array $abilities = ['*'], array $extra = []): NewAccessToken{
         $token = $this->tokens()->create([
             'name' => $name,
             'token' => hash('sha256', $plainTextToken = Str::random(40)),
             'abilities' => $abilities,
             'extra' => $extra,
         ]);

         return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
     }
}
