<?php

use App\Modules\Auth\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
