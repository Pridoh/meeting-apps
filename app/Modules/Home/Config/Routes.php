<?php

use App\Modules\Home\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landingPage.index');
})->name('home');

Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');
