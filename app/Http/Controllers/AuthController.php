<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use function bcrypt;
use function response;

class AuthController extends Controller{
    public function logout(Request $request){
        $user = $request->user();
        $token = $user->currentAccessToken();
        $token->delete();
        return response()->json(true);
    }

    public function putChangePassword(Request $request){
        $user = $request->user();
        $data = $this->validate($request, [
            'old_password'=>[
                'required','string',function($attr,$value,$fail) use ($user){
                    if(!Hash::check($value,$user->password)){
                        $fail('原始密码错误');
                    }
                }
            ],
            'new_password'=>['required','confirmed'],
        ]);
        $user->password = bcrypt($data['new_password']);
        $user->save();
        return response()->json(true);

    }

    public function user(Request $request){
        return response()->json($request->user());
    }

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

    public function login(LoginRequest $request){
        return response()->json($request->authenticate());
    }
}
