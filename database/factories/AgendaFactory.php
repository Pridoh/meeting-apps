<?php

namespace Database\Factories;

use App\Modules\Meetings\Models\Agenda;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Agenda> */
class AgendaFactory extends Factory
{
    protected $model = Agenda::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'title' => fake()->sentence(3),
            'order_index' => 1,
        ];
    }
}
