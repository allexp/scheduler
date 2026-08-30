<?php
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::middleware('api.auth')->group(function(){
    Route::get('/me',[AuthController::class,'me']);Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/dashboard',[MetaController::class,'dashboard']);Route::get('/employees',[MetaController::class,'employees']);
    Route::apiResource('clients',ClientController::class);Route::apiResource('appointments',AppointmentController::class);
    Route::post('/{type}/{id}/comments',[CommentController::class,'store'])->whereIn('type',['clients','appointments']);Route::delete('/comments/{comment}',[CommentController::class,'destroy']);
    Route::get('/notifications',[NotificationController::class,'index']);Route::patch('/notifications/{notification}/read',[NotificationController::class,'read']);
    Route::middleware('role:admin')->group(function(){
        Route::get('/history',[MetaController::class,'history']);
        Route::apiResource('users',UserController::class)->except('show');
    });
});
