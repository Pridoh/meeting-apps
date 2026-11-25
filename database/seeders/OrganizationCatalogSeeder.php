<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Rooms\Models\Organization;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $owner = User::where('email', 'organizer@rapatin.test')->firstOrFail();
        DB::transaction(function () use ($owner): void {
            $organization = Organization::firstOrCreate([
                'owner_id' => $owner->id, 'name' => 'POLITEKNIK ELEKTRONIKA NEGERI SURABAYA',
            ], [
                'category' => 'educational_institution', 'join_code' => Organization::newJoinCode(),
            ]);

            $legacyRooms = Room::whereNull('building_id')->where('location', 'Gedung Demo')
                ->whereIn('name', ['Ruang Diskusi', 'Ruang Seminar'])->pluck('id');
            $demoBuilding = $this->building($organization, 'Gedung Demo');
            Room::whereIn('id', $legacyRooms)->update(['building_id' => $demoBuilding]);

            $legacyRoomIds = Room::where('building_id', $demoBuilding)->pluck('id');
            $members = DB::table('room_reviewers')->whereIn('room_id', $legacyRoomIds)->pluck('user_id')
                ->merge(DB::table('room_requests')->whereIn('room_id', $legacyRoomIds)->pluck('requester_id'))
                ->merge(User::whereIn('email', ['organizer@rapatin.test', 'reviewer@rapatin.test'])->pluck('id'))
                ->unique();

            foreach ($members as $userId) {
                DB::table('organization_memberships')->insertOrIgnore([
                    'organization_id' => $organization->id, 'user_id' => $userId, 'created_at' => now(),
                ]);
            }

            foreach (['Gedung SAW' => ['04 06', '03 07', '04 07'], 'Gedung D3' => ['JJ 303', 'HH 202']] as $name => $rooms) {
                $buildingId = $this->building($organization, $name);
                foreach ($rooms as $roomName) {
                    DB::table('rooms')->insertOrIgnore([
                        'building_id' => $buildingId, 'name' => $roomName, 'location' => $name,
                        'capacity' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    private function building(Organization $organization, string $name): int
    {
        DB::table('buildings')->insertOrIgnore([
            'organization_id' => $organization->id, 'name' => $name, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('buildings')->where('organization_id', $organization->id)->where('name', $name)->value('id');
    }
}
