<?php

namespace App\Modules\Participation\Controllers;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Requests\SaveAttendanceRequest;
use App\Modules\Participation\Services\AttendanceRecap;
use App\Modules\Participation\Services\AttendanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceController
{
    public function index(Request $request, AttendanceService $attendance): View
    {
        $now = now()->toImmutable();

        return view('attendance.index', [
            'options' => $attendance->options($request->user()->email, $request->user()->id, $now),
            'now' => $now,
        ]);
    }

    public function store(SaveAttendanceRequest $request, Meeting $meeting, AttendanceService $attendance, AttendanceRecap $recap): RedirectResponse
    {
        $recap->captureDue();
        $attendance->save($request->user(), $meeting, $request->validated('choice'), $request->integer('schedule_version'));

        return to_route('attendance.index')->with('success', 'Pilihan kehadiran disimpan.');
    }

    public function summary(Meeting $meeting, AttendanceRecap $recap): View
    {
        Gate::authorize('update', $meeting);
        abort_unless($meeting->mode === 'offline', 404);
        $recap->captureDue();

        return view('attendance.summary', [
            'meeting' => $meeting,
            'current' => $recap->counts($meeting, now()->toImmutable()),
            'snapshot' => $recap->snapshot($meeting),
            'deadlineAvailable' => $recap->deadlineWasAvailable($meeting),
        ]);
    }
}
