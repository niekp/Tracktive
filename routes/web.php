<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return \Illuminate\Support\Facades\Redirect::route('activities.index');
});

Route::match(['GET', 'POST'], '/login', \App\Http\Controllers\LoginController::class)->name('login');

Route::get('/login/set-token', [\App\Http\Controllers\LoginController::class, 'login'])
    ->middleware('signed')
    ->name('login.set-token');

Route::middleware('auth:web')->group(function () {
    Route::resource('activities', \App\Http\Controllers\ActivityController::class);
    Route::get('activities/{activity}/download', [\App\Http\Controllers\ActivityController::class, 'download'])->name('activities.download');
    Route::post('activities/{activity}/favorite', [\App\Http\Controllers\ActivityController::class, 'favorite'])->name('activities.favorite');
    Route::resource('gps', \App\Http\Controllers\GpsController::class);
    Route::get('gps/{index}/download', [\App\Http\Controllers\GpsController::class, 'download'])->name('gps.download');
});

Route::post('capture', [\App\Http\Controllers\ActivityController::class, 'capture']);
