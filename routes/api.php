<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',[AuthController::class,'login']);
Route::apiResource('/test', TestController::class);

Route::middleware('auth:sanctum')->group(function(){

Route::post('/logout',[AuthController::class,'logout']);
Route::get('/user',[AuthController::class,'user']);

});

