<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Require the separated route files
require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/apartments.php';
require __DIR__ . '/api/bookings.php';
require __DIR__ . '/api/admin.php';
