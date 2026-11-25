<?php

namespace App\Modules\Rooms\Policies;

use App\Models\User;
use App\Modules\Rooms\Models\RoomRequest;
use Illuminate\Support\Facades\DB;

class RoomRequestPolicy
{
    public function view(User $user, RoomRequest $booking): bool
    {
        return ($user->id === $booking->requester_id && $this->belongsToRoomOrganization($user, $booking))
            || $this->review($user, $booking);
    }

    public function review(User $user, RoomRequest $booking): bool
    {
        return $user->id !== $booking->requester_id
            && $booking->room->building_id !== null
            && $this->belongsToRoomOrganization($user, $booking)
            && $booking->submitted_at !== null
            && DB::table('room_reviewers')->where('room_id', $booking->room_id)->where('user_id', $user->id)->exists();
    }

    public function withdraw(User $user, RoomRequest $booking): bool
    {
        return $user->id === $booking->requester_id && $this->belongsToRoomOrganization($user, $booking);
    }

    private function belongsToRoomOrganization(User $user, RoomRequest $booking): bool
    {
        return $booking->room->building_id === null || DB::table('buildings')
            ->join('organization_memberships', 'organization_memberships.organization_id', '=', 'buildings.organization_id')
            ->where('buildings.id', $booking->room->building_id)->where('organization_memberships.user_id', $user->id)->exists();
    }
}
