<?php

namespace App\Modules\Meetings\Models;

use App\Models\User;
use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\Meetings\Policies\MeetingPolicy;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'title', 'description', 'start_time', 'end_time', 'timezone', 'mode', 'location'])]
#[UseFactory(MeetingFactory::class)]
#[UsePolicy(MeetingPolicy::class)]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory, SoftDeletes;

    public const DEFAULT_TIMEZONE = 'Asia/Jakarta';

    public const STATUSES = ['scheduled', 'ongoing', 'completed'];

    public const MODES = ['online', 'offline'];

    protected $attributes = [
        'timezone' => self::DEFAULT_TIMEZONE,
        'room_status' => 'none',
        'status' => 'scheduled',
        'schedule_version' => 1,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_time' => 'immutable_datetime',
            'end_time' => 'immutable_datetime',
            'schedule_version' => 'integer',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class)->orderBy('order_index')->orderBy('id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_participants')->withTimestamps();
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(ActionItem::class);
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants()->whereKey($user->id)->exists();
    }

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        $query->where(fn (Builder $meetings): Builder => $meetings->whereBelongsTo($user, 'organizer')
            ->orWhereHas('participants', fn (Builder $participants): Builder => $participants->whereKey($user->id)));
    }

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('status', 'scheduled')->where('start_time', '>=', now());
    }
}
