<?php

namespace App\Modules\Rooms\Models;

use App\Models\User;
use App\Modules\Rooms\Policies\OrganizationPolicy;
use Database\Factories\Modules\Rooms\Models\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Hidden(['join_code'])]
#[UseFactory(OrganizationFactory::class)]
#[UsePolicy(OrganizationPolicy::class)]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'category', 'join_code'];

    public const CATEGORIES = [
        'private_workspace' => 'Private Workspace',
        'company' => 'Company',
        'educational_institution' => 'Educational Institution',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_memberships');
    }

    public function availableRooms(): Builder
    {
        return Room::where('is_active', true)->whereIn('building_id', DB::table('buildings')
            ->select('id')->where('organization_id', $this->id)->where('is_active', true));
    }

    public static function newJoinCode(): string
    {
        return Str::upper(Str::random(16));
    }
}
