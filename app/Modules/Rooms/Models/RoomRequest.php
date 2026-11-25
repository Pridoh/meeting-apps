<?php

namespace App\Modules\Rooms\Models;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Rooms\Policies\RoomRequestPolicy;
use Database\Factories\RoomRequestFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(RoomRequestPolicy::class)]
#[UseFactory(RoomRequestFactory::class)]
class RoomRequest extends Model
{
    /** @use HasFactory<RoomRequestFactory> */
    use HasFactory;

    public const EDITABLE = ['draft', 'needs_revision', 'rejected'];

    public const STATUSES = [
        'draft' => 'Draf', 'submitted' => 'Menunggu persetujuan',
        'needs_revision' => 'Perlu perbaikan', 'approved' => 'Disetujui',
        'rejected' => 'Ditolak', 'withdrawn' => 'Ditarik', 'superseded' => 'Tidak berlaku',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'immutable_datetime', 'end_time' => 'immutable_datetime',
            'version' => 'integer', 'people_count' => 'integer', 'submitted_at' => 'immutable_datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class)->withTrashed();
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
