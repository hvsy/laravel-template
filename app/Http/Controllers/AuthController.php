<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller{
    public function logout(Request $request){
        $user = $request->user();
        $token = $user->currentAccessToken();
        $token->delete();
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
