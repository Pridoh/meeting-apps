<?php

use App\Modules\ActionItems\Controllers\ActionItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/action-items', [ActionItemController::class, 'index'])->name('action-items.index');
    Route::patch('/meetings/{meeting}/action-items/{actionItem}', [ActionItemController::class, 'update'])
        ->scopeBindings()
        ->name('meetings.action-items.update');
});
