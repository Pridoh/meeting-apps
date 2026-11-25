<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Rooms\Models\Organization;
use App\Modules\Rooms\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RoomBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_explains_why_an_owned_meeting_cannot_be_selected(): void
    {
        $owner = User::factory()->create();
        $upcoming = Meeting::factory()->create(['user_id' => $owner->id, 'title' => 'Rapat mendatang']);
        Meeting::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Rapat yang sudah dimulai',
            'start_time' => now()->subMinute(),
        ]);
        $other = Meeting::factory()->create();

        $this->actingAs($owner)->get(route('rooms.index'))
            ->assertOk()
            ->assertSeeText($upcoming->title)
            ->assertSee('<option disabled>Rapat yang sudah dimulai · meeting sudah dimulai</option>', false)
            ->assertDontSeeText($other->title);
    }

    public function test_catalog_and_booking_follow_the_selected_organization(): void
    {
        Storage::fake('local');
        $meeting = Meeting::factory()->create();
        $room = $this->room();
        $otherRoom = $this->room();
        $otherRoom->forceFill(['name' => 'Ruang organisasi lain'])->save();
        $this->join($meeting->organizer, $room);
        $this->actingAs($meeting->organizer);

        $this->get(route('rooms.index', ['organization' => $room->organizationId(), 'meeting' => $meeting->id]))
            ->assertOk()->assertSee('Ruang Uji')->assertDontSee('Ruang organisasi lain');
        $this->get(route('rooms.index', ['organization' => $otherRoom->organizationId()]))->assertNotFound();
        $this->send($meeting, $otherRoom)->assertNotFound();

        $this->join($meeting->organizer, $otherRoom);
        $this->get(route('rooms.index', ['organization' => $otherRoom->organizationId(), 'meeting' => $meeting->id]))
            ->assertOk()->assertSee('Ruang organisasi lain')->assertDontSee('Ruang Uji');
        $this->send($meeting, $otherRoom, ['organization_id' => $room->organizationId()])->assertNotFound();
        $this->send($meeting, $otherRoom)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('room_requests', ['meeting_id' => $meeting->id, 'room_id' => $otherRoom->id]);
    }

    public function test_submission_checks_capacity_documents_and_private_access(): void
    {
        Storage::fake('local');
        $room = $this->room();
        $meeting = Meeting::factory()->create();
        $this->join($meeting->organizer, $room);
        $this->actingAs($meeting->organizer);
        $this->get(route('rooms.index', ['meeting' => $meeting->id, 'people_count' => 21]))
            ->assertOk()->assertSee('Kurang 1 kursi');
        $this->send($meeting, $room, ['people_count' => 21])->assertSessionHasErrors('people_count');
        $this->send($meeting, $room, ['attachments' => []])->assertSessionHasErrors('attachments');
        $this->send($meeting, $room, ['attachments' => [UploadedFile::fake()->create('surat.txt', 1, 'text/plain')]])
            ->assertSessionHasErrors('attachments.0');
        $this->send($meeting, $room)->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table('room_requests')->sole()->id;
        $attachment = DB::table('room_request_attachments')->sole();
        $this->assertSame('pending', $meeting->fresh()->room_status);
        $this->get(route('room-requests.show', $id))->assertOk()->assertSee('Peninjau belum tersedia');
        $url = route('room-requests.attachment', [$id, $attachment->id]);
        $this->get($url)->assertDownload('surat.png');

        $outsider = User::factory()->create();
        $meeting->participants()->attach($outsider);
        $this->actingAs($outsider)->get($url)->assertForbidden();
        $this->decide($id)->assertForbidden();
        DB::table('room_reviewers')->insert(['room_id' => $room->id, 'user_id' => $meeting->user_id]);
        $this->actingAs($meeting->organizer);
        $this->decide($id)->assertForbidden();
    }

    public function test_a_draft_can_be_updated_submitted_and_withdrawn(): void
    {
        Storage::fake('local');
        $meeting = Meeting::factory()->create();
        $room = $this->room();
        $this->join($meeting->organizer, $room);
        $this->actingAs($meeting->organizer);
        $this->get(route('room-requests.form', [$meeting, 'room' => $room->id, 'organization' => $room->organizationId()]))->assertOk();
        $this->send($meeting, $room, ['intent' => 'draft', 'purpose' => null])->assertSessionHasNoErrors();
        $booking = DB::table('room_requests')->sole();
        $this->assertSame('draft', $booking->status);
        $this->assertSame('none', $meeting->fresh()->room_status);

        $this->send($meeting, $room, ['version' => 1, 'attachments' => []])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('room_request_attachments', ['room_request_id' => $booking->id, 'request_version' => 2]);
        $this->assertSame('pending', $meeting->fresh()->room_status);
        $this->post(route('room-requests.withdraw', $booking->id), ['version' => 2])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('room_requests', ['id' => $booking->id, 'status' => 'withdrawn']);
        $this->assertDatabaseCount('room_reservations', 0);
        $this->assertSame('none', $meeting->fresh()->room_status);
    }

    public function test_decisions_follow_versions_conflict_rules_and_confirmed_schedule_changes(): void
    {
        Notification::fake();
        Storage::fake('local');
        $room = $this->room();
        $reviewer = User::factory()->create();
        $this->join($reviewer, $room);
        DB::table('room_reviewers')->insert(['room_id' => $room->id, 'user_id' => $reviewer->id]);
        $start = now()->addDays(3)->startOfHour();
        $a = Meeting::factory()->create(['start_time' => $start, 'end_time' => $start->copy()->addHour()]);
        $b = Meeting::factory()->create(['start_time' => $start, 'end_time' => $start->copy()->addHour()]);
        $adjacent = Meeting::factory()->create(['start_time' => $a->end_time, 'end_time' => $start->copy()->addHours(2)]);
        foreach ([$a, $b, $adjacent] as $meeting) {
            $this->join($meeting->organizer, $room);
            $this->actingAs($meeting->organizer);
            $this->send($meeting, $room)->assertSessionHasNoErrors();
        }

        $requests = DB::table('room_requests')->pluck('id', 'meeting_id');
        $this->actingAs($reviewer)->get(route('room-requests.review'))->assertOk();
        $this->decide($requests[$a->id], 'needs_revision')->assertSessionHasNoErrors();
        $this->actingAs($a->organizer);
        $this->get(route('room-requests.show', $requests[$a->id]))->assertOk()->assertSee('Keputusan pengujian');
        $this->send($a, $room, ['version' => 1])->assertSessionHasNoErrors();
        $this->actingAs($reviewer);
        $this->decide($requests[$a->id])->assertSessionHasErrors('version');
        $this->decide($requests[$a->id], 'approved', 2)->assertSessionHasNoErrors();
        $this->decide($requests[$b->id])->assertSessionHasErrors('room_id');
        $this->assertDatabaseHas('room_requests', ['id' => $requests[$b->id], 'status' => 'submitted']);
        $this->decide($requests[$b->id], 'rejected')->assertSessionHasNoErrors();
        $this->decide($requests[$adjacent->id])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('room_reservations', 2);

        $this->actingAs($a->organizer)->get(route('meetings.edit', $a))->assertOk()->assertSee('Reservasi akan dilepas');
        $change = ['title' => $a->title, 'mode' => 'offline', 'timezone' => 'UTC',
            'start_time' => $start->copy()->addDay()->format('Y-m-d\TH:i'),
            'end_time' => $start->copy()->addDay()->addHour()->format('Y-m-d\TH:i')];
        $this->put(route('meetings.update', $a), $change)->assertSessionHasErrors('confirm_room_release');
        $this->assertSame('confirmed', $a->fresh()->room_status);
        $this->put(route('meetings.update', $a), $change + ['confirm_room_release' => '1'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('room_reservations', ['room_request_id' => $requests[$a->id], 'status' => 'released']);
        $this->assertDatabaseHas('room_requests', ['id' => $requests[$a->id], 'status' => 'superseded']);
        $this->assertSame('none', $a->fresh()->room_status);
    }

    private function room(): Room
    {
        $organization = Organization::factory()->create();
        $building = DB::table('buildings')->insertGetId(['organization_id' => $organization->id, 'name' => 'Gedung A']);
        $room = new Room;
        $room->forceFill(['building_id' => $building, 'name' => 'Ruang Uji', 'location' => 'Gedung A', 'capacity' => 20, 'is_active' => true])->save();

        return $room;
    }

    private function join(User $user, Room $room): void
    {
        DB::table('organization_memberships')->insert([
            'organization_id' => $room->organizationId(), 'user_id' => $user->id, 'created_at' => now(),
        ]);
    }

    private function send(Meeting $meeting, Room $room, array $overrides = []): TestResponse
    {
        return $this->post(route('room-requests.store', $meeting), array_replace([
            'organization_id' => $room->organizationId(), 'room_id' => $room->id, 'people_count' => 20, 'purpose' => 'Rapat tim',
            'intent' => 'submit', 'version' => 0, 'schedule_version' => $meeting->schedule_version,
            'attachments' => [UploadedFile::fake()->image('surat.png')],
        ], $overrides));
    }

    private function decide(int $id, string $result = 'approved', int $version = 1): TestResponse
    {
        return $this->post(route('room-requests.decide', $id), [
            'result' => $result, 'version' => $version, 'reason' => 'Keputusan pengujian',
        ]);
    }
}
