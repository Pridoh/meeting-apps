<?php

use App\Modules\Rooms\Controllers\OrganizationController;
use App\Modules\Rooms\Controllers\RoomBookingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->controller(RoomBookingController::class)->group(function (): void {
    Route::get('/rooms', 'index')->name('rooms.index');
    Route::get('/rooms/review', 'review')->name('room-requests.review');
    Route::get('/meetings/{meeting}/room-request', 'form')->name('room-requests.form');
    Route::post('/meetings/{meeting}/room-request', 'store')->name('room-requests.store');
    Route::get('/room-requests/{booking}', 'show')->name('room-requests.show');
    Route::post('/room-requests/{booking}/decision', 'decide')->name('room-requests.decide');
    Route::post('/room-requests/{booking}/withdraw', 'withdraw')->name('room-requests.withdraw');
    Route::get('/room-requests/{booking}/attachments/{attachment}', 'download')
        ->whereNumber('attachment')->name('room-requests.attachment');
});

Route::middleware('auth')->controller(OrganizationController::class)
    ->prefix('organizations')->name('organizations.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/join', 'join')->middleware('throttle:10,1')->name('join');
        Route::get('/{organization}', 'show')->name('show');
        Route::post('/{organization}/buildings', 'saveBuilding')->name('buildings.store');
        Route::patch('/{organization}/buildings/{building}', 'saveBuilding')->whereNumber('building')->name('buildings.update');
        Route::post('/{organization}/rooms', 'saveRoom')->name('rooms.store');
        Route::patch('/{organization}/rooms/{room}', 'saveRoom')->name('rooms.update');
        Route::post('/{organization}/rooms/{room}/applications', 'apply')->name('manager.apply');
        Route::post('/{organization}/applications/{application}/decision', 'decide')->whereNumber('application')->name('manager.decide');
    });
