<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:sanctum'],function(){
    Route::post('logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/user', [AuthController::class,'user']);
    Route::put('change-password',[AuthController::class,'putChangePassword'])->name('change.password');
});

Route::middleware('guest')->post('login',[AuthController::class,'login'])->name('login');
Route::middleware('guest')->post('register',[AuthController::class,'register'])->name('register');
