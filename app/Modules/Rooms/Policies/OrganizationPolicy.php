<?php

namespace App\Modules\Rooms\Policies;

use App\Models\User;
use App\Modules\Rooms\Models\Organization;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $organization->members()->whereKey($user->id)->exists();
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->id === $organization->owner_id && $this->view($user, $organization);
    }
}
