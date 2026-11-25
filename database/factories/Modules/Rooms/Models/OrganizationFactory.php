<?php

namespace Database\Factories\Modules\Rooms\Models;

use App\Models\User;
use App\Modules\Rooms\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Organization> */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company(),
            'category' => 'private_workspace',
            'join_code' => Organization::newJoinCode(),
        ];
    }
}
