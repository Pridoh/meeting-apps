<?php

namespace App\Modules\Rooms\Controllers;

use App\Modules\Rooms\Models\Organization;
use App\Modules\Rooms\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrganizationController
{
    public function index(Request $request): View
    {
        return view('rooms.organizations', [
            'organizations' => Organization::whereHas('members', fn ($query) => $query->whereKey($request->user()->id))->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(Organization::CATEGORIES))],
        ]);
        $organization = DB::transaction(function () use ($request, $data): Organization {
            do {
                $organization = Organization::createOrFirst(
                    ['join_code' => Organization::newJoinCode()],
                    [...$data, 'owner_id' => $request->user()->id],
                );
            } while (! $organization->wasRecentlyCreated);

            $organization->members()->attach($request->user()->id, ['created_at' => now()]);

            return $organization;
        });

        return to_route('organizations.show', $organization);
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate(['join_code' => ['required', 'string', 'max:24']]);
        $organization = Organization::where('join_code', Str::upper(trim($data['join_code'])))->first();
        if (! $organization) {
            throw ValidationException::withMessages(['join_code' => 'Kode bergabung tidak valid.']);
        }
        DB::table('organization_memberships')->insertOrIgnore([
            'organization_id' => $organization->id, 'user_id' => $request->user()->id, 'created_at' => now(),
        ]);

        return to_route('organizations.show', $organization)->with('success', 'Keanggotaan organisasi aktif.');
    }

    public function show(Request $request, Organization $organization): View
    {
        Gate::authorize('view', $organization);
        $owner = $request->user()->can('update', $organization);
        $buildings = DB::table('buildings')->where('organization_id', $organization->id)->orderBy('name')->get();
        $rooms = Room::whereIn('building_id', $buildings->pluck('id'))->orderBy('name')->get();
        $catalogBuildings = $owner ? $buildings : $buildings->where('is_active', true);
        $applications = DB::table('room_manager_applications as applications')
            ->join('users', 'users.id', '=', 'applications.applicant_id')
            ->join('rooms', 'rooms.id', '=', 'applications.room_id')
            ->whereIn('applications.room_id', $rooms->pluck('id'))
            ->when(! $owner, fn ($query) => $query->where('applicant_id', $request->user()->id))
            ->latest('applications.id')->get(['applications.*', 'users.name as applicant_name', 'rooms.name as room_name']);

        return view('rooms.organization', [
            'organization' => $organization, 'owner' => $owner, 'buildings' => $catalogBuildings,
            'rooms' => $rooms->groupBy('building_id'), 'applications' => $applications,
            'members' => $owner ? $organization->members()->orderBy('name')->get(['users.id', 'users.name']) : collect(),
            'managedRooms' => DB::table('room_reviewers')->where('user_id', $request->user()->id)
                ->whereIn('room_id', $rooms->pluck('id'))->pluck('room_id'),
        ]);
    }

    public function saveBuilding(Request $request, Organization $organization, ?int $building = null): RedirectResponse
    {
        Gate::authorize('update', $organization);
        $buildings = DB::table('buildings')->where('organization_id', $organization->id);
        abort_if($building !== null && ! (clone $buildings)->where('id', $building)->exists(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('buildings')->where('organization_id', $organization->id)->ignore($building)],
            'location' => ['nullable', 'string', 'max:160'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        DB::transaction(function () use ($organization, $buildings, $building, $data): void {
            if ($building === null) {
                DB::table('buildings')->insert([...$data, 'organization_id' => $organization->id, 'created_at' => now(), 'updated_at' => now()]);

                return;
            }
            DB::table('rooms')->where('building_id', $building)->orderBy('id')->lockForUpdate()->get(['id']);
            $buildings->where('id', $building)->update([...$data, 'updated_at' => now()]);
        });

        return back()->with('success', 'Gedung disimpan.');
    }

    public function saveRoom(Request $request, Organization $organization, ?Room $room = null): RedirectResponse
    {
        Gate::authorize('update', $organization);
        if ($room?->exists) {
            $this->ensureRoom($organization, $room);
        }
        $data = $request->validate([
            'building_id' => ['required', 'integer', Rule::exists('buildings', 'id')->where('organization_id', $organization->id)],
            'name' => ['required', 'string', 'max:120', Rule::unique('rooms')->where('building_id', $request->integer('building_id'))->ignore($room?->id)],
            'location' => ['nullable', 'string', 'max:160'],
            'capacity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['location'] = $data['location'] ?? '';

        ($room?->exists ? $room : new Room)->forceFill($data)->save();

        return back()->with('success', 'Ruang disimpan.');
    }

    public function apply(Request $request, Organization $organization, Room $room): RedirectResponse
    {
        Gate::authorize('view', $organization);
        $this->ensureRoom($organization, $room);
        $userId = $request->user()->id;
        DB::transaction(function () use ($room, $userId): void {
            $room = Room::lockForUpdate()->findOrFail($room->id);
            abort_unless($room->isAvailableInCatalog(), 409);
            if (DB::table('room_reviewers')->where('room_id', $room->id)->where('user_id', $userId)->exists()
                || DB::table('room_manager_applications')->where('room_id', $room->id)->where('applicant_id', $userId)->where('status', 'pending')->exists()) {
                return;
            }
            DB::table('room_manager_applications')->insert([
                'room_id' => $room->id, 'applicant_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }, attempts: 3);

        return back()->with('success', 'Pengajuan manajer ruang tercatat.');
    }

    public function decide(Request $request, Organization $organization, int $application): RedirectResponse
    {
        Gate::authorize('update', $organization);
        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        DB::transaction(function () use ($request, $organization, $application, $data): void {
            $applications = DB::table('room_manager_applications');
            $record = (clone $applications)->where('id', $application)->first();
            abort_unless($record, 404);
            $room = Room::lockForUpdate()->findOrFail($record->room_id);
            $this->ensureRoom($organization, $room);
            $record = (clone $applications)->where('id', $application)->lockForUpdate()->first();
            if ($record->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Pengajuan ini sudah diputuskan.']);
            }
            if ($data['status'] === 'approved') {
                abort_unless($organization->members()->whereKey($record->applicant_id)->exists(), 409);
                DB::table('room_reviewers')->insertOrIgnore(['room_id' => $room->id, 'user_id' => $record->applicant_id]);
            }
            $applications->where('id', $application)->update([
                'status' => $data['status'], 'decided_by' => $request->user()->id, 'decided_at' => now(), 'updated_at' => now(),
            ]);
        }, attempts: 3);

        return back()->with('success', 'Keputusan pengajuan manajer disimpan.');
    }

    private function ensureRoom(Organization $organization, Room $room): void
    {
        abort_unless(DB::table('buildings')->where('id', $room->building_id)->where('organization_id', $organization->id)->exists(), 404);
    }
}
