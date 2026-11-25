<?php

namespace App\Modules\ActionItems\Models;

use App\Models\User;
use App\Modules\ActionItems\Policies\ActionItemPolicy;
use App\Modules\Meetings\Models\Meeting;
use Database\Factories\ActionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assignee_user_id', 'description', 'deadline', 'status'])]
#[UseFactory(ActionItemFactory::class)]
#[UsePolicy(ActionItemPolicy::class)]
class ActionItem extends Model
{
    /** @use HasFactory<ActionItemFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'in_progress', 'completed'];

    protected $attributes = ['status' => 'pending'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['deadline' => 'immutable_date'];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }

    #[Scope]
    protected function assignedTo(Builder $query, User $user): void
    {
        $query->whereBelongsTo($user, 'assignee')
            ->whereHas('meeting.participants', fn (Builder $participants): Builder => $participants->whereKey($user->id));
    }

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->where(fn (Builder $tasks): Builder => $tasks->assignedTo($user)
            ->orWhereHas('meeting', fn (Builder $meetings): Builder => $meetings->whereBelongsTo($user, 'organizer')));
    }
}
