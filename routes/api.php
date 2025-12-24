<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;


use APP\Http\Controllers;
use App\Models\Apartment;
use Illuminate\Support\Facades\Route;

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('check_phone_availability', [UserController::class, 'checkAvailableNumber']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('self', [UserController::class, "getUser"]);
});
Route::get('all_apartments', [ApartmentController::class, 'getAllApartments']);


Route::post('login_admin', [AdminController::class, 'login_admin']);

Route::middleware(['auth:admin','abilities:admin'])->group(function(){
      Route::get('get_All_Users',[AdminController::class,'get_All_Users']);
      Route::delete('delete_user/{id}',[AdminController::class,'delete_user']);
      Route::patch('accept_user/{id}',[AdminController::class,'Accept_user']);
      Route::patch('reject_user/{id}',[AdminController::class,'Reject_user']);
}); 
