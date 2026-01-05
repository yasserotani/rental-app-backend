<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Booking
    Route::post('/bookings', [BookingController::class, 'createBook']);
    Route::put('/bookings/{id}/update', [BookingController::class, 'update']);
    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{id}/reject', [BookingController::class, 'reject']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('/bookings/user_bookings', [BookingController::class, 'getAllUserBookings']);
    Route::get('bookings/owner', [BookingController::class, 'getAllOwnerBookings']);
    Route::get('owner/bookings', [BookingController::class, 'getAllOwnerBookings']); // Alias for Flutter compatibility
});
