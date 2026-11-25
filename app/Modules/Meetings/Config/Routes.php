<?php

use App\Modules\Meetings\Controllers\AgendaController;
use App\Modules\Meetings\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::resource('meetings', MeetingController::class);
    Route::patch('/meetings/{meeting}/status', [MeetingController::class, 'status'])->name('meetings.status');

    Route::prefix('/meetings/{meeting}/agendas')->name('meetings.agendas.')->group(function (): void {
        Route::post('/', [AgendaController::class, 'store'])->name('store');
        Route::patch('/reorder', [AgendaController::class, 'reorder'])->name('reorder');
        Route::patch('/{agenda}', [AgendaController::class, 'update'])->whereNumber('agenda')->name('update');
        Route::delete('/{agenda}', [AgendaController::class, 'destroy'])->whereNumber('agenda')->name('destroy');
    });
});
