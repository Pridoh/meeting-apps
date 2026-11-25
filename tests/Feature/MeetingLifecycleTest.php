<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Meetings\Events\MeetingScheduleChanged;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Meetings\Notifications\MeetingChanged;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MeetingLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_organizer_signs_in_creates_a_meeting_reorders_agenda_and_completes_it(): void
    {
        $this->withoutVite();
        $organizer = User::factory()->create();
        $this->travelTo(now()->setDate(2026, 12, 1)->startOfDay());

        $this->post('/login', [
            'email' => $organizer->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->post('/meetings', [
            'title' => 'Rapat evaluasi',
            'start_time' => '2026-12-02T10:00',
            'end_time' => '2026-12-02T11:00',
            'timezone' => 'Asia/Jakarta',
            'mode' => 'offline',
            'location' => 'Ruang A',
            'status' => 'completed',
        ])->assertRedirect();

        $meeting = Meeting::sole();
        $this->assertSame($organizer->id, $meeting->user_id);
        $this->assertSame('2026-12-02 03:00:00', $meeting->start_time->toDateTimeString());
        $this->assertSame('scheduled', $meeting->status);
        $meetingUrl = '/meetings/'.$meeting->id;

        $this->post($meetingUrl.'/agendas', ['title' => 'Laporan'])->assertRedirect($meetingUrl);
        $this->post($meetingUrl.'/agendas', ['title' => 'Tindak lanjut'])->assertRedirect($meetingUrl);
        [$firstAgenda, $secondAgenda] = $meeting->agendas()->pluck('id')->all();

        $this->patch($meetingUrl.'/agendas/reorder', [
            'agenda_ids' => [$firstAgenda],
        ])->assertSessionHasErrors('agenda_ids');

        $this->patch($meetingUrl.'/agendas/reorder', [
            'agenda_ids' => [$secondAgenda, $firstAgenda],
        ])->assertRedirect($meetingUrl);
        $this->assertSame([$secondAgenda, $firstAgenda], $meeting->agendas()->pluck('id')->all());

        $this->patch($meetingUrl.'/agendas/'.$secondAgenda, ['title' => 'Tindak lanjut tim'])
            ->assertRedirect($meetingUrl);
        $this->assertDatabaseHas('agendas', ['id' => $secondAgenda, 'title' => 'Tindak lanjut tim']);
        $this->delete($meetingUrl.'/agendas/'.$firstAgenda)->assertRedirect($meetingUrl);
        $this->assertDatabaseMissing('agendas', ['id' => $firstAgenda]);

        $this->get('/dashboard')->assertOk()
            ->assertViewHas('organizerStats', ['total' => 1, 'upcoming' => 1, 'completed' => 0, 'pendingTasks' => 0]);

        $this->patch($meetingUrl.'/status', ['status' => 'completed'])->assertSessionHasErrors('status');
        $this->patch($meetingUrl.'/status', ['status' => 'ongoing'])->assertRedirect($meetingUrl);
        $this->patch($meetingUrl.'/status', ['status' => 'completed'])->assertRedirect($meetingUrl);
        $this->patch($meetingUrl.'/status', ['status' => 'ongoing'])->assertSessionHasErrors('status');

        $this->assertSame('completed', $meeting->fresh()->status);
        $this->get('/dashboard')->assertOk()
            ->assertViewHas('organizerStats', ['total' => 1, 'upcoming' => 0, 'completed' => 1, 'pendingTasks' => 0]);

        $this->post('/logout')->assertRedirect('/login');
        $this->get($meetingUrl)->assertRedirect('/login');
    }

    public function test_meeting_requires_a_valid_interval_and_normalizes_its_timezone(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/meetings', [
            'title' => 'Rapat evaluasi',
            'start_time' => '2026-12-02T11:00',
            'end_time' => '2026-12-02T10:00',
            'mode' => 'online',
        ])->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('meetings', 0);

        $this->post('/meetings', [
            'title' => 'Rapat melewati perubahan jam',
            'start_time' => '2026-03-08T02:30',
            'end_time' => '2026-03-08T04:00',
            'timezone' => 'America/New_York',
            'mode' => 'online',
        ])->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('meetings', 0);
    }

    public function test_only_a_schedule_change_increments_version_and_deletion_removes_access(): void
    {
        $organizer = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = Meeting::factory()->for($organizer, 'organizer')->create([
            'start_time' => '2026-12-02 03:00:00',
            'end_time' => '2026-12-02 04:00:00',
            'mode' => 'offline',
        ]);
        $meeting->participants()->attach($participant);
        Event::fake([MeetingScheduleChanged::class]);
        Notification::fake();

        $details = [
            'title' => 'Judul pengganti',
            'start_time' => '2026-12-02T10:00',
            'end_time' => '2026-12-02T11:00',
            'timezone' => 'Asia/Jakarta',
            'mode' => 'offline',
        ];
        $meetingUrl = '/meetings/'.$meeting->id;
        $this->actingAs($organizer)->put($meetingUrl, $details)->assertRedirect($meetingUrl);
        $this->assertSame(1, $meeting->fresh()->schedule_version);
        Event::assertNotDispatched(MeetingScheduleChanged::class);

        $this->put($meetingUrl, [...$details, 'mode' => 'online'])->assertRedirect($meetingUrl);
        $this->assertSame(2, $meeting->fresh()->schedule_version);
        Event::assertDispatched(MeetingScheduleChanged::class, fn (MeetingScheduleChanged $event): bool => $event->meetingId === $meeting->id && $event->scheduleVersion === 2);
        Notification::assertSentTo($participant, MeetingChanged::class);

        $this->delete($meetingUrl)->assertRedirect('/meetings');
        $this->assertSoftDeleted($meeting);
        $this->actingAs($participant)->get($meetingUrl)->assertNotFound();
        Notification::assertSentToTimes($participant, MeetingChanged::class, 2);
    }
}
