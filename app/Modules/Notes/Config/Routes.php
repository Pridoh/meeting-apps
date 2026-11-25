<?php

use App\Modules\Notes\Controllers\NotesController;
use Illuminate\Support\Facades\Route;

Route::prefix('meetings/{meeting}/notes')->name('notes.')->middleware('auth')->controller(NotesController::class)->group(function () {
    Route::get('/', 'show')->middleware('can:view,meeting')->name('show');
    Route::middleware('can:update,meeting')->group(function () {
        Route::put('draft', 'draft')->name('draft');
        Route::post('generate', 'generate')->name('generate');
        Route::put('review', 'review')->name('review');
        Route::post('finalize', 'finalize')->name('finalize');
    });
});
