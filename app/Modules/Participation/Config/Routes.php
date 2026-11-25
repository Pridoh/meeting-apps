<?php

use App\Modules\Participation\Controllers\MeetingParticipantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/meetings/{meeting}/participants')->name('meetings.participants.')->group(function (): void {
    Route::post('/', [MeetingParticipantController::class, 'store'])->name('store');
    Route::delete('/{participant}', [MeetingParticipantController::class, 'destroy'])->whereNumber('participant')->name('destroy');
});
