<?php

namespace App\Http\Controllers\Api\Authed;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Put;

/**
 * 已登录状态下的 API 接口
 */
class AuthController extends Controller
{
    #[Post('logout','logout')]
    public function logout(Request $request){
        $user = $request->user();
        $token = $user->currentAccessToken();
        $token->delete();
        return response()->json(true);
    }
    
    #[Put('change-password','change.password')]
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
            'password'=>['required','confirmed'],
        ]);
        $user->password = bcrypt($data['password']);
        $user->save();
        return response()->json(true);
        
    }
    
    #[Get('user')]
    public function user(Request $request){
        $user = $request->user();
        $user->append('faker');
        return response()->json($request->user());
    }
    
    #[Post('fake/{user}')]
    public function postFake(Request $request,User $user){
        $current = $request->user();
        $token = $current->switchTo($user);
        return res()->json($token);
    }
    
    #[Delete('fake')]
    public function deleteFake(Request $request){
        $current = $request->user();
        $to = $current->getFakerAttribute();
        if(!empty($to)){
            $token = $current->switchTo($to);
        }
        return res()->json($token);
    }
}
