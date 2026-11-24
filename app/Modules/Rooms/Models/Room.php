<?php

namespace App\Modules\Rooms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'location', 'capacity', 'is_active'])]
class Room extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
