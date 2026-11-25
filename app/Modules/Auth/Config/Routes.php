<?php

use App\Modules\Auth\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});

use App\Modules\Auth\Controllers\RegistrationController;

Route::middleware('guest')->group(function () {
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:10,1');
});
Route::middleware('auth')->group(function () {
    Route::view('/email/verify', 'auth.verify')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [RegistrationController::class, 'verify'])->middleware(['signed', 'throttle:12,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [RegistrationController::class, 'resend'])->middleware('throttle:3,1')->name('verification.send');
    Route::post('/invitation/switch-account', [RegistrationController::class, 'switchAccount'])->name('invitation.switch');
});
