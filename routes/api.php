<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// auth
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('check_phone_availability', [UserController::class, 'checkAvailableNumber']);

Route::get('apartments', [ApartmentController::class, 'getAllApartments']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('self', [UserController::class, 'getUser']);

    Route::post('create_apartment', [ApartmentController::class, 'createApartments']);
});
Route::get('user_apartments', [ApartmentController::class, 'getUserApartments']);



// POST   /api/register                  - Register new user
// POST   /api/login                     - Login user
// POST   /api/check_phone_availability  - Check if phone number is available
// GET    /api/all_apartments            - Get all apartments with images
// GET    /api/user_apartments?user_id=1 - Get apartments for specific user
// POST   /api/logout                    - Logout (requires auth)
// GET    /api/self                      - Get current user data (requires auth)
// POST   /api/create_apartment          - create a new apartment for the user ,( required auth)