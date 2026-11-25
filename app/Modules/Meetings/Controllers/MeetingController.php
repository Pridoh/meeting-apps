<?php

namespace App\Modules\Meetings\Controllers;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Meetings\Requests\SaveMeetingRequest;
use App\Modules\Meetings\Requests\UpdateMeetingStatusRequest;
use App\Modules\Meetings\Services\MeetingService;
use App\Modules\Notes\Models\MeetingNote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MeetingController
{
    public function index(Request $request): View
    {
        return view('meetings.index', [
            'meetings' => Meeting::visibleTo($request->user())
                ->with('organizer')
                ->orderByDesc('start_time')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('meetings.create');
    }

    public function store(SaveMeetingRequest $request): RedirectResponse
    {
        $meeting = Meeting::create([...$request->meetingData(), 'user_id' => $request->user()->id]);

        return to_route('meetings.show', $meeting)->with('success', 'Meeting dibuat.');
    }

    public function show(Request $request, Meeting $meeting): View
    {
        Gate::authorize('view', $meeting);
        $isOrganizer = $request->user()->can('update', $meeting);
        $meeting->load('organizer', 'agendas');
        if ($isOrganizer) {
            $meeting->load('participants');
        }

        return view('meetings.show', [
            'meeting' => $meeting,
            'isOrganizer' => $isOrganizer,
            'canOpenNotes' => $isOrganizer || MeetingNote::where('meeting_id', $meeting->id)->where('is_finalized', true)->exists(),
            'actionItems' => $meeting->actionItems()->visibleTo($request->user())->with('assignee')->get(),
        ]);
    }

    public function edit(Meeting $meeting): View
    {
        Gate::authorize('update', $meeting);

        return view('meetings.edit', ['meeting' => $meeting]);
    }

    public function update(SaveMeetingRequest $request, Meeting $meeting, MeetingService $service): RedirectResponse
    {
        $service->update($meeting, $request->meetingData());

        return to_route('meetings.show', $meeting)->with('success', 'Meeting diperbarui.');
    }

    public function status(UpdateMeetingStatusRequest $request, Meeting $meeting, MeetingService $service): RedirectResponse
    {
        $service->updateStatus($meeting, $request->validated('status'), $request->user()->id);

        return to_route('meetings.show', $meeting)->with('success', 'Status meeting diperbarui.');
    }

    public function destroy(Meeting $meeting, MeetingService $service): RedirectResponse
    {
        Gate::authorize('delete', $meeting);
        $service->delete($meeting);

        return to_route('meetings.index')->with('success', 'Meeting dihapus.');
    }
}
