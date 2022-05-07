<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest{

    public function authorize(): bool{
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array{
        return [
            'account' => ['required', 'string',],
            'password' => ['required', 'string'],
            'device.uuid'=> 'required',
            'device.extra' => 'array',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     *
     * @throws ValidationException
     */
    public function authenticate(){
        $this->ensureIsNotRateLimited();

        $user = User::where('account', $this->input('account'))->first();

        if(!$user || !Hash::check($this->input('password'), $user->password)){
            throw ValidationException::withMessages([
                'account' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $user->createToken($this->input('device')['uuid'], ['*'], $this->input('device')['extra'] ?? []);
        RateLimiter::clear($this->throttleKey());
        return $token->plainTextToken;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void{
        if(!RateLimiter::tooManyAttempts($this->throttleKey(), 5)){
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'account' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string{
        return Str::lower($this->input('account')) . '|' . $this->ip();
    }
}
