<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('User_Register', [UserController::class, 'register']);
Route::post('User_login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('User_logout', [UserController::class, 'logout']);
});
