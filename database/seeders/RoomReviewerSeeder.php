<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomReviewerSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $reviewer = User::firstOrCreate(['email' => 'reviewer@rapatin.test'], [
            'name' => 'Peninjau Ruang Demo',
            'password' => config('auth.demo_password') ?: Str::random(32),
        ]);

        foreach (Room::where('location', 'Gedung Demo')->whereIn('name', ['Ruang Diskusi', 'Ruang Seminar'])
            ->whereNull('building_id')->pluck('id') as $roomId) {
            DB::table('room_reviewers')->insertOrIgnore(['room_id' => $roomId, 'user_id' => $reviewer->id]);
        }
    }
}
