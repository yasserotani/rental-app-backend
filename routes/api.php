<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use App\Models\Apartment;
use Illuminate\Support\Facades\Route;

// auth
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('check_phone_availability', [UserController::class, 'checkAvailableNumber']);

Route::get('apartments', [ApartmentController::class, 'getAllApartments']);

// api that need auth
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('self', [UserController::class, 'getUser']);

    // apartment
    Route::post('create_apartment', [ApartmentController::class, 'createApartments']);
    Route::get('apartment_bookings/{id}', [ApartmentController::class, 'getAllApartmentBookings']);
    Route::get('/apartments', [ApartmentController::class, 'search']);
    // Booking 
    Route::post('/bookings', [BookingController::class, 'createBook']);
    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{id}/reject', [BookingController::class, 'reject']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('/bookings/user_bookings', [BookingController::class, 'getAllUserBookings']);

    // Reviews
    Route::post('/apartments/{apartment_id}/review', [UserController::class, 'review']);
});

Route::post('login_admin', [AdminController::class, 'login_admin']);

// admin
Route::middleware(['auth:admin', 'abilities:admin'])->group(function () {
    Route::get('get_All_Users', [AdminController::class, 'get_All_Users']);
    Route::delete('delete_user/{id}', [AdminController::class, 'delete_user']);
    Route::patch('accept_user/{id}', [AdminController::class, 'Accept_user']);
    Route::patch('reject_user/{id}', [AdminController::class, 'Reject_user']);
    Route::get('user/{id}', [AdminController::class, 'get_user']);
    Route::get('pending_users', [AdminController::class, 'getAllPendingsUsers']);
});

Route::get('user_apartments', [ApartmentController::class, 'getUserApartments']);

//2|kPGTzy2XWr4Vfm7ZGVrhU3m8M3XKsVlJfc341D9y970f7cfd