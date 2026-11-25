<?php

namespace App\Modules\Rooms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'location', 'capacity', 'is_active'])]
class Room extends Model
{
    public function organizationId(): ?int
    {
        return DB::table('buildings')->where('id', $this->building_id)->value('organization_id');
    }

    public function isAvailableInCatalog(): bool
    {
        return $this->is_active && DB::table('buildings')->where('id', $this->building_id)->where('is_active', true)->exists();
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
