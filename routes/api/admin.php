<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::post('login_admin', [AdminController::class, 'login_admin']);

Route::middleware(['auth:admin', 'abilities:admin'])->group(function () {
    Route::get('get_All_Users', [AdminController::class, 'get_All_Users']);
    Route::delete('delete_user/{id}', [AdminController::class, 'delete_user']);
    Route::patch('accept_user/{id}', [AdminController::class, 'Accept_user']);
    Route::patch('reject_user/{id}', [AdminController::class, 'Reject_user']);
    Route::get('user/{id}', [AdminController::class, 'get_user']);
    Route::get('pending_users', [AdminController::class, 'getAllPendingsUsers']);
});
