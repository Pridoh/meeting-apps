<?php

namespace App\Modules\Notes\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingNote extends Model
{
    protected function casts(): array
    {
        return ['ai_raw_json' => 'array', 'reviewed_data' => 'array', 'is_finalized' => 'boolean', 'finalized_at' => 'datetime'];
    }
}
