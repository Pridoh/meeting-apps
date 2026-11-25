<?php

namespace Database\Factories;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Rooms\Models\RoomRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/** @extends Factory<RoomRequest> */
class RoomRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'requester_id' => fn (array $data): int => Meeting::findOrFail($data['meeting_id'])->user_id,
            'room_id' => fn (): int => DB::table('rooms')->insertGetId([
                'name' => fake()->unique()->word(), 'location' => 'Gedung Uji', 'capacity' => 40,
            ]),
            'start_time' => fn (array $data) => Meeting::findOrFail($data['meeting_id'])->start_time,
            'end_time' => fn (array $data) => Meeting::findOrFail($data['meeting_id'])->end_time,
            'purpose' => fake()->sentence(),
            'people_count' => 10,
            'status' => 'draft',
            'version' => 1,
        ];
    }
}
