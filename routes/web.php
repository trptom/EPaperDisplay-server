<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisplayController;
use App\Models\Display;

Route::get('/', function () {
    return view('welcome');
});

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
