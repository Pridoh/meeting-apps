<?php

namespace App\Modules\Participation\Models;

use App\Modules\Meetings\Models\Meeting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    protected function casts(): array
    {
        return ['expires_at' => 'immutable_datetime', 'last_sent_at' => 'immutable_datetime', 'responded_at' => 'immutable_datetime'];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(InvitationDelivery::class)->latest('id');
    }
}
