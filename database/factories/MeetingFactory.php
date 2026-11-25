<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Meeting> */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
            'mode' => 'offline',
        ];
    }
}
