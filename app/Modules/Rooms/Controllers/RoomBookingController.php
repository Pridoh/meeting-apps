<?php

namespace App\Modules\Rooms\Controllers;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Services\AttendanceRecap;
use App\Modules\Rooms\Models\Organization;
use App\Modules\Rooms\Models\RoomRequest;
use App\Modules\Rooms\Requests\SaveRoomRequest;
use App\Modules\Rooms\Services\RoomBooking;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoomBookingController
{
    public function index(Request $request, RoomBooking $service): View
    {
        $request->validate([
            'organization' => ['nullable', 'integer'],
            'meeting' => ['nullable', 'integer'],
            'people_count' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'start' => ['nullable', 'required_with:end', 'date_format:Y-m-d\TH:i'],
            'end' => ['nullable', 'required_with:start', 'date_format:Y-m-d\TH:i', 'after:start'],
        ]);
        $ownedMeetings = Meeting::where('user_id', $request->user()->id);
        $now = now();
        $meetings = (clone $ownedMeetings)->where('mode', 'offline')
            ->where('status', 'scheduled')->where('start_time', '>', $now)->orderBy('start_time')->get();
        $unavailableMeetings = $ownedMeetings->where(fn ($query) => $query
            ->where('mode', '!=', 'offline')->orWhere('status', '!=', 'scheduled')->orWhere('start_time', '<=', $now))
            ->latest('id')->limit(5)->get();
        $meeting = $request->filled('meeting') ? $meetings->find($request->integer('meeting')) : null;
        abort_if($request->filled('meeting') && ! $meeting, 404);
        $people = $request->integer('people_count', 1);
        $start = $meeting?->start_time;
        $end = $meeting?->end_time;
        if ($request->filled('start')) {
            $start = CarbonImmutable::parse($request->input('start'), Meeting::DEFAULT_TIMEZONE)->utc();
            $end = CarbonImmutable::parse($request->input('end'), Meeting::DEFAULT_TIMEZONE)->utc();
        }

        $organizations = Organization::whereHas('members', fn ($query) => $query->whereKey($request->user()->id))
            ->orderBy('name')->get();
        $organization = $request->filled('organization')
            ? $organizations->find($request->integer('organization'))
            : $organizations->first();
        abort_if($request->filled('organization') && ! $organization, 404);
        $rooms = $organization?->availableRooms()->orderBy('name')->get() ?? collect();
        $buildings = DB::table('buildings')->whereIn('id', $rooms->pluck('building_id'))->pluck('name', 'id');

        $pending = collect();
        $reserved = collect();
        if ($start && $end) {
            $pending = $service->overlaps('room_requests', $start, $end)->whereIn('room_id', $rooms->pluck('id'))->where('status', 'submitted')
                ->selectRaw('room_id, count(*) as total')->groupBy('room_id')->pluck('total', 'room_id');
            $reserved = $service->overlaps('room_reservations', $start, $end)->whereIn('room_id', $rooms->pluck('id'))->where('status', 'active')->pluck('room_id');
        }

        return view('rooms.index', [
            'rooms' => $rooms, 'buildings' => $buildings, 'organizations' => $organizations, 'organization' => $organization,
            'canReview' => $service->roomsForReviewer($request->user())->exists(),
            'meetings' => $meetings, 'unavailableMeetings' => $unavailableMeetings,
            'meeting' => $meeting, 'people' => $people,
            'start' => $start, 'end' => $end, 'pending' => $pending, 'reserved' => $reserved,
        ]);
    }

    public function form(Request $request, Meeting $meeting, RoomBooking $service, AttendanceRecap $recap): View|RedirectResponse
    {
        Gate::authorize('update', $meeting);
        abort_unless($meeting->mode === 'offline' && $meeting->status === 'scheduled', 409);
        $current = $service->latest($meeting);
        if ($current && in_array($current->status, ['submitted', 'approved'], true)) {
            return to_route('room-requests.show', $current);
        }

        $booking = $current && in_array($current->status, RoomRequest::EDITABLE, true) ? $current : null;
        $organizationId = $request->integer('organization', $booking?->room->organizationId() ?? 0);
        if (! $organizationId) {
            return to_route('rooms.index', ['meeting' => $meeting->id]);
        }
        $organization = Organization::findOrFail($organizationId);
        Gate::authorize('view', $organization);
        $rooms = $organization->availableRooms()->orderBy('name')->get();
        $selectedRoom = $rooms->find($request->integer('room', $booking?->room_id ?? 0));
        abort_if($request->filled('room') && ! $selectedRoom, 404);
        $buildings = DB::table('buildings')->whereIn('id', $rooms->pluck('building_id'))->pluck('name', 'id');
        $confirmed = $recap->counts($meeting, now()->toImmutable())['confirmed_count'];

        return view('rooms.form', [
            'meeting' => $meeting, 'booking' => $booking, 'rooms' => $rooms, 'selectedRoom' => $selectedRoom,
            'organization' => $organization, 'buildings' => $buildings, 'confirmed' => $confirmed, 'attachments' => $booking ? $service->attachments($booking)->get() : collect(),
        ]);
    }

    public function store(SaveRoomRequest $request, Meeting $meeting, RoomBooking $service): RedirectResponse
    {
        $booking = $service->save($meeting, $request->validated());

        return to_route('room-requests.show', $booking)->with('success',
            $booking->status === 'draft' ? 'Draf pengajuan disimpan.' : 'Pengajuan dikirim untuk ditinjau.');
    }

    public function show(RoomRequest $booking, RoomBooking $service): View
    {
        Gate::authorize('view', $booking);
        $booking->load('meeting', 'room', 'requester');
        $hasReviewer = DB::table('room_reviewers')->where('room_id', $booking->room_id)
            ->where('user_id', '!=', $booking->requester_id)->exists();

        return view('rooms.show', [
            'booking' => $booking, 'hasReviewer' => $hasReviewer,
            'attachments' => $service->attachments($booking)->get(),
            'decisions' => DB::table('room_request_decisions')->where('room_request_id', $booking->id)
                ->when($booking->requester_id !== auth()->id(), fn ($query) => $query->where('request_version', $booking->version))
                ->latest('request_version')->get(),
        ]);
    }

    public function review(Request $request, RoomBooking $service): View
    {
        $rooms = $service->roomsForReviewer($request->user());
        abort_unless((clone $rooms)->exists(), 403);

        return view('rooms.review', ['bookings' => RoomRequest::with('room', 'meeting', 'requester')
            ->whereIn('room_id', $rooms)->where('requester_id', '!=', $request->user()->id)
            ->whereHas('room', fn ($query) => $query->where(fn ($rooms) => $rooms
                ->whereNull('building_id')->orWhereIn('building_id', DB::table('buildings')->select('buildings.id')
                ->join('organization_memberships', 'organization_memberships.organization_id', '=', 'buildings.organization_id')
                ->where('organization_memberships.user_id', $request->user()->id))))
            ->where('status', 'submitted')->oldest('submitted_at')->paginate(15)]);
    }

    public function decide(Request $request, RoomRequest $booking, RoomBooking $service): RedirectResponse
    {
        Gate::authorize('review', $booking);
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'],
            'result' => ['required', 'in:needs_revision,approved,rejected'],
            'reason' => ['required', 'string', 'max:3000'],
        ]);
        $service->decide($booking, $request->user(), (int) $data['version'], $data['result'], $data['reason']);

        return to_route('room-requests.show', $booking)->with('success', 'Keputusan disimpan.');
    }

    public function withdraw(Request $request, RoomRequest $booking, RoomBooking $service): RedirectResponse
    {
        Gate::authorize('withdraw', $booking);
        $request->validate(['version' => ['required', 'integer', 'min:1'], 'confirm_room_release' => ['sometimes', 'accepted']]);
        $service->withdraw($booking, $request->integer('version'), $request->boolean('confirm_room_release'));

        return to_route('room-requests.show', $booking)->with('success', 'Pengajuan ditutup.');
    }

    public function download(RoomRequest $booking, int $attachment, RoomBooking $service): StreamedResponse
    {
        Gate::authorize('view', $booking);
        $file = $service->attachments($booking)->where('id', $attachment)->first();
        abort_unless($file && Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->original_name, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
