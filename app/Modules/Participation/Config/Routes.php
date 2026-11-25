<?php

use App\Modules\Participation\Controllers\AttendanceController;
use App\Modules\Participation\Controllers\MeetingParticipantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/meetings/{meeting}/participants')->name('meetings.participants.')->group(function (): void {
    Route::post('/', [MeetingParticipantController::class, 'store'])->name('store');
    Route::delete('/{participant}', [MeetingParticipantController::class, 'destroy'])->whereNumber('participant')->name('destroy');
});

use App\Modules\Participation\Controllers\InvitationController;

Route::controller(InvitationController::class)->group(function () {
    Route::get('/invitation', 'show')->name('invitation.show');
    Route::post('/invitation/open', 'open')->middleware('throttle:60,1')->name('invitation.open');
    Route::post('/invitation/{answer}', 'respond')->whereIn('answer', ['accepted', 'declined'])->middleware(['auth', 'verified'])->name('invitation.respond');
    Route::middleware(['auth', 'can:update,meeting'])->prefix('meetings/{meeting}/invitations/{id}')->group(function () {
        Route::post('resend', 'resend')->name('invitations.resend');
        Route::delete('/', 'revoke')->name('invitations.revoke');
    });
});

Route::middleware(['auth', 'verified'])->controller(AttendanceController::class)->group(function (): void {
    Route::get('/attendance', 'index')->name('attendance.index');
    Route::post('/attendance/{meeting}', 'store')->name('attendance.store');
    Route::get('/meetings/{meeting}/attendance', 'summary')->name('attendance.summary');
});
