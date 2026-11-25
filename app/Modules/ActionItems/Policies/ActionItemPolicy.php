<?php

namespace App\Modules\ActionItems\Policies;

use App\Models\User;
use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\Meetings\Models\Meeting;

class ActionItemPolicy
{
    public function update(User $user, ActionItem $actionItem, Meeting $meeting): bool
    {
        return $this->assign($user, $actionItem, $meeting)
            || ($actionItem->meeting_id === $meeting->id
                && $actionItem->assignee_user_id === $user->id
                && $meeting->hasParticipant($user));
    }

    public function assign(User $user, ActionItem $actionItem, Meeting $meeting): bool
    {
        return $actionItem->meeting_id === $meeting->id && $user->can('update', $meeting);
    }
}
