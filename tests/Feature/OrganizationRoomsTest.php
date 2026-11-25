<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Rooms\Models\Organization;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrganizationRoomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pens_demo_catalog_is_idempotent_and_keeps_legacy_room_ids(): void
    {
        $oldRoomId = DB::table('rooms')->insertGetId([
            'name' => 'Ruang Diskusi', 'location' => 'Gedung Demo', 'capacity' => 12,
        ]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $organization = Organization::where('category', 'educational_institution')->sole();
        $buildings = DB::table('buildings')->where('organization_id', $organization->id)->pluck('id', 'name');
        $this->assertSame($buildings['Gedung Demo'], DB::table('rooms')->where('id', $oldRoomId)->value('building_id'));
        $this->assertSame(['03 07', '04 06', '04 07'], DB::table('rooms')->where('building_id', $buildings['Gedung SAW'])->orderBy('name')->pluck('name')->all());
        $this->assertSame(['HH 202', 'JJ 303'], DB::table('rooms')->where('building_id', $buildings['Gedung D3'])->orderBy('name')->pluck('name')->all());

        $reviewer = User::where('email', 'reviewer@rapatin.test')->sole();
        $this->assertDatabaseHas('room_reviewers', ['room_id' => $oldRoomId, 'user_id' => $reviewer->id]);
        $this->actingAs($reviewer)->get(route('organizations.show', $organization))->assertOk()->assertSee('Gedung SAW');
    }

    public function test_memberships_catalogs_and_room_manager_approvals_are_scoped(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $this->actingAs($owner);
        foreach (Organization::CATEGORIES as $category => $label) {
            $this->post(route('organizations.store'), ['name' => $label, 'category' => $category])->assertRedirect();
        }
        $first = Organization::where('category', 'educational_institution')->sole();
        $other = Organization::where('category', 'company')->sole();
        $this->assertSame(3, Organization::distinct()->count('join_code'));
        $this->post(route('organizations.buildings.store', $first), ['name' => 'Gedung SAW'])->assertSessionHasNoErrors();
        $building = DB::table('buildings')->sole();
        foreach (['04 06', '03 07'] as $name) {
            $this->post(route('organizations.rooms.store', $first), [
                'building_id' => $building->id, 'name' => $name, 'capacity' => 30,
            ])->assertSessionHasNoErrors();
        }
        $this->post(route('organizations.rooms.store', $other), [
            'building_id' => $building->id, 'name' => 'Outside', 'capacity' => 30,
        ])->assertSessionHasErrors('building_id');
        $rooms = DB::table('rooms')->orderBy('id')->pluck('id');

        $this->actingAs($member)->get(route('organizations.show', $first))->assertForbidden();
        $this->post(route('organizations.join'), ['join_code' => 'INVALID'])->assertSessionHasErrors('join_code');
        foreach ([$first, $first, $other] as $organization) {
            $this->post(route('organizations.join'), ['join_code' => strtolower($organization->join_code)])->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('organization_memberships', 5);
        $this->get(route('organizations.show', $first))->assertOk()->assertSee('04 06')->assertDontSee($first->join_code);
        $this->post(route('organizations.manager.apply', [$other, $rooms[0]]))->assertNotFound();
        $this->post(route('organizations.buildings.store', $first), ['name' => 'Outside'])->assertForbidden();
        foreach ([$rooms[0], $rooms[0], $rooms[1]] as $room) {
            $this->post(route('organizations.manager.apply', [$first, $room]))->assertRedirect();
        }
        $this->assertDatabaseCount('room_manager_applications', 2);
        $applications = DB::table('room_manager_applications')->pluck('id', 'room_id');
        $approve = route('organizations.manager.decide', [$first, $applications[$rooms[0]]]);
        $this->post($approve, ['status' => 'approved'])->assertForbidden();
        $this->actingAs($owner)->post($approve, ['status' => 'approved'])->assertSessionHasNoErrors();
        $this->post($approve, ['status' => 'approved'])->assertSessionHasErrors('status');
        $this->post(route('organizations.manager.decide', [$first, $applications[$rooms[1]]]), ['status' => 'rejected'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('room_reviewers', ['room_id' => $rooms[0], 'user_id' => $member->id]);
        $this->assertDatabaseMissing('room_reviewers', ['room_id' => $rooms[1], 'user_id' => $member->id]);
    }
}
