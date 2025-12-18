<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\UserController;
use APP\Http\Controllers;
use App\Models\Apartment;
use Illuminate\Support\Facades\Route;
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('self', [UserController::class, "getUser"]);
});
Route::get('all_apartments', [ApartmentController::class, 'getAllApartments']);