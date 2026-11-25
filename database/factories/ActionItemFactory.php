<?php

namespace Database\Factories;

use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ActionItem> */
class ActionItemFactory extends Factory
{
    protected $model = ActionItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'description' => fake()->sentence(),
        ];
    }
}
