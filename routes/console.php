<?php

use App\Modules\Participation\Services\AttendanceRecap;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Modules\Participation\Models\Invitation;
use Illuminate\Support\Facades\Schedule;

Artisan::command('rapatin:expire-invitations', function (AttendanceRecap $recap) {
    $recap->captureDue();
    $count = Invitation::where('status', 'pending')->where('expires_at', '<=', now())->update(['status' => 'expired']);
    $this->info("Undangan kedaluwarsa: {$count}");
});
Schedule::command('rapatin:expire-invitations')->hourly();

Artisan::command('rapatin:snapshot-h1', function (AttendanceRecap $recap): void {
    $recap->captureDue();
})->purpose('Simpan rekap kehadiran H-1 yang jatuh tempo');

Schedule::command('rapatin:snapshot-h1')->everyFiveMinutes()->withoutOverlapping();
