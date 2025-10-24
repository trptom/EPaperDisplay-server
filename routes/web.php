<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DisplayController;
use App\Models\Display;

// Public display image by UUID/token (route-model binding to Display->token)
Route::get('/display/{displayToken}', [DisplayController::class, 'get'])
    ->where('displayToken', '[0-9a-fA-F\-]{36}');

// Numeric ID routes: bind {displayId} to Display by primary key
Route::model('displayId', Display::class);
Route::get('/display/{displayId}/get', [DisplayController::class, 'getData'])
    ->where('displayId', '[0-9]+')
    ->middleware('auth');
Route::post('/display/{displayId}/set', [DisplayController::class, 'setData'])
    ->where('displayId', '[0-9]+')
    ->middleware('auth');

// Create a new Display (authenticated)
Route::post('/display/create', [DisplayController::class, 'create'])->middleware('auth');

// OAuth routes for Google
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// OAuth routes for Facebook
Route::get('/auth/facebook/redirect', [AuthController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// Standard login/logout/getInfo
Route::get('/auth/user', [AuthController::class, 'user']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
