<?php

namespace App\Modules\ActionItems\Services;

use App\Models\User;
use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ActionItemService
{
    /** @param array{status: string, assignee_user_id?: int|string|null} $changes */
    public function update(Meeting $meeting, ActionItem $actionItem, User $actor, array $changes): void
    {
        DB::transaction(function () use ($meeting, $actionItem, $actor, $changes): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $task = $current->actionItems()->findOrFail($actionItem->id);
            Gate::forUser($actor)->authorize('update', [$task, $current]);

            if (array_key_exists('assignee_user_id', $changes)) {
                Gate::forUser($actor)->authorize('assign', [$task, $current]);
                if ($changes['assignee_user_id'] !== null && ! $current->participants()->whereKey($changes['assignee_user_id'])->exists()) {
                    throw ValidationException::withMessages(['assignee_user_id' => 'Penanggung jawab harus menjadi peserta meeting ini.']);
                }
            } elseif ($changes['status'] !== 'completed' && $task->assignee_user_id !== null
                && ! $current->participants()->whereKey($task->assignee_user_id)->exists()) {
                $changes['assignee_user_id'] = null;
            }

            $task->update($changes);
            if ($task->wasChanged('status')) {
                DB::afterCommit(fn () => Log::info('Action item status updated', [
                    'action_item_id' => $task->id,
                    'actor_id' => $actor->id,
                    'status' => $task->status,
                ]));
            }
        });
    }
}
