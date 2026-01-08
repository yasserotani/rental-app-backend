<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\ReviewsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Apartments
    Route::get('apartments', [ApartmentController::class, 'getAllApartments']);
    Route::get('my_apartments', [ApartmentController::class, 'getUserApartments']);
    Route::post('apartment', [ApartmentController::class, 'createApartments']);
    Route::put('/apartments/{id}', [ApartmentController::class, 'updateApartment']);
    Route::post('/apartments/{id}/images', [ApartmentController::class, 'addImages']);
    Route::delete('/apartments/{id}/images', [ApartmentController::class, 'deleteImages']);
    Route::get('apartment_bookings/{id}', [ApartmentController::class, 'getAllApartmentBookings']);
    Route::get('/apartments/search', [ApartmentController::class, 'search']);
    Route::delete('apartment', [ApartmentController::class, 'delete']);

    // Reviews
    Route::post('/apartments/{apartment_id}/review', [ReviewsController::class, 'review']);
    Route::get('/my-reviews', [ReviewsController::class, 'getMyReviews']);

    // Favorites
    Route::post('/favorites/{apartmentId}/toggle', [FavoritesController::class, 'toggleFavorite']);
    Route::get('/favorites/', [FavoritesController::class, 'getAllUserFavorites']);
});
