<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use function response;

/**
 * 未登录下的 api 接口
 */
class AuthController extends Controller{
    #[Post('register','register','guest')]
    public function register(Request $request){
        $data = $this->validate($request, [
            'account' => ['required','string','unique:users'],
            'password' => ['required','confirmed',Rules\Password::defaults()],
            'agreement'=>'accepted',
            'device.uuid'=> 'required',
            'device.extra' => 'array',
        ]);
        $user = User::create([
            'account'=>$data['account'],
            'password'=>Hash::make($data['password'])
        ]);
        event(new Registered($user));
        $token = $user->createToken($data['device']['uuid'], ['*'], $data['device']['extra'] ?? []);
        return response()->json($token->plainTextToken);
    }

    
    #[Post('login','login','guest')]
    public function login(LoginRequest $request){
        return response()->json($request->authenticate());
    }
}
