<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach (['organizer' => 'Organizer Demo', 'peserta' => 'Peserta Demo'] as $account => $name) {
            User::firstOrCreate(['email' => $account.'@rapatin.test'], [
                'name' => $name,
                'password' => config('auth.demo_password') ?: Str::random(32),
            ]);
        }

        foreach (['Ruang Diskusi' => 12, 'Ruang Seminar' => 40] as $name => $capacity) {
            Room::firstOrCreate(['location' => 'Gedung Demo', 'name' => $name], ['capacity' => $capacity]);
        }

        $this->call([RoomReviewerSeeder::class, OrganizationCatalogSeeder::class]);
    }
}
