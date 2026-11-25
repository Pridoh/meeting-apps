<?php

namespace App\Modules\Meetings\Policies;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting) || $meeting->hasParticipant($user);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->id === $meeting->user_id;
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting);
    }
}
