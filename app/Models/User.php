<?php

namespace App\Models;

use App\Scopes\UserScopes;
use App\Traits\ModelScopes;
use App\Traits\Searchable;
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
        UserScopes,
        ModelScopes,
        Searchable,
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

    
    public function casts()
    {
        return [
            'account_verified_at' => 'datetime',
        ];
    }
    
    public function createToken(string $name, array $abilities = ['*'], array $extra = []): NewAccessToken{
         $token = $this->tokens()->create([
             'name' => $name,
             'token' => hash('sha256', $plainTextToken = Str::random(40)),
             'abilities' => $abilities,
             'extra' => $extra,
         ]);

         return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
     }
    
    
    public function getFakerAttribute(){
        return $this->accessToken->faker;
    }
    
    public function switchTo(User $user) : CustomPersonalAccessToken{
        /**
         * @var CustomPersonalAccessToken $token
         */
        $token = $this->accessToken;
        $token->tokenable()->associate($user);
        if(empty($token->faker)){
            $token->faker()->associate($this);
        }else if($token->faker->id === $user->id){
            $token->faker()->dissociate();
        }
        $token->save();
        return $token;
    }
}
