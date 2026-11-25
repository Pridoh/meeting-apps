<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhaseOneMeetingSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $this->call(DatabaseSeeder::class);
        $organizer = User::where('email', 'organizer@rapatin.test')->firstOrFail();
        $participant = User::where('email', 'peserta@rapatin.test')->firstOrFail();

        DB::transaction(function () use ($organizer, $participant): void {
            $meeting = Meeting::firstOrCreate([
                'user_id' => $organizer->id,
                'title' => 'Evaluasi Persiapan Kegiatan',
            ], [
                'start_time' => now()->addDay(),
                'end_time' => now()->addDay()->addHour(),
                'mode' => 'offline',
                'location' => 'Ruang Diskusi',
            ]);

            if ($meeting->wasRecentlyCreated) {
                $meeting->agendas()->create(['title' => 'Tinjau kesiapan tim', 'order_index' => 1]);
                $meeting->participants()->attach($participant);
            }
        });
    }
}
