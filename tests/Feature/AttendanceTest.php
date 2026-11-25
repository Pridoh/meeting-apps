<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Meetings\Notifications\MeetingChanged;
use App\Modules\Meetings\Services\MeetingService;
use App\Modules\Participation\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_choices_replace_each_other_and_adjacent_meetings_can_coexist(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:00', 'UTC'));
        $person = User::factory()->create();
        $a = $this->meeting('09:00', '10:00', $person);
        $b = $this->meeting('09:30', '10:30', $person);
        $c = $this->meeting('10:00', '11:00', $person);

        $this->actingAs($person)->get(route('attendance.index'))->assertOk()->assertSee('Jadwal bertumpuk');
        $this->choose($a);
        $this->choose($c);
        $this->assertDatabaseHas('attendance_choices', ['meeting_id' => $a->id, 'choice' => 'will_attend']);
        $this->choose($b);
        foreach ([$a, $c] as $meeting) {
            $this->assertSame('not_chosen', DB::table('attendance_choices')->where('meeting_id', $meeting->id)->latest('id')->value('choice'));
        }
        $this->assertDatabaseHas('meeting_participants', ['meeting_id' => $b->id, 'user_id' => $person->id]);
        $this->actingAs(User::factory()->create())->post(route('attendance.store', $b), [
            'choice' => 'will_attend', 'schedule_version' => 1,
        ])->assertForbidden();
    }

    public function test_snapshot_includes_exact_deadline_and_stays_fixed_after_late_corrections(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:00', 'UTC'));
        $person = User::factory()->create();
        $latePerson = User::factory()->create();
        $a = $this->meeting('09:00', '10:00', $person, $latePerson);
        $b = $this->meeting('09:30', '10:30', $person, $latePerson);

        $this->travelTo($a->start_time->subDay());
        $this->artisan('rapatin:snapshot-h1')->assertSuccessful();
        $this->actingAs($person);
        $this->choose($a);
        $this->travel(1)->seconds();
        $this->actingAs($latePerson);
        $this->choose($b);
        $this->assertDatabaseHas('attendance_choices', ['meeting_id' => $b->id, 'user_id' => $latePerson->id, 'is_late' => true]);

        $this->travelTo($b->start_time->subDay()->addSecond());
        $this->artisan('rapatin:snapshot-h1')->assertSuccessful();
        $this->assertDatabaseHas('attendance_snapshots', ['meeting_id' => $a->id, 'confirmed_count' => 1, 'uncertain_count' => 1]);
        $this->assertDatabaseHas('attendance_snapshots', ['meeting_id' => $b->id, 'confirmed_count' => 0, 'uncertain_count' => 1]);
        $this->actingAs($b->organizer)->get(route('attendance.summary', $b))->assertOk()->assertSee('+1');
        $this->actingAs($a->organizer)->get(route('attendance.summary', $b))->assertForbidden();
    }

    public function test_schedule_change_requires_reconfirmation_and_removing_an_alternative_closes_conflict(): void
    {
        Notification::fake();
        $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:00', 'UTC'));
        $person = User::factory()->create();
        $a = $this->meeting('09:00', '10:00', $person);
        $b = $this->meeting('10:00', '11:00', $person);
        $this->actingAs($person);
        $this->choose($a);
        $this->choose($b);

        $this->travelTo($a->start_time->subDay()->addSecond());
        $details = $a->only(['title', 'description', 'timezone', 'mode', 'location']);
        app(MeetingService::class)->update($a, [...$details, 'start_time' => '2026-12-03 09:30:00', 'end_time' => '2026-12-03 10:30:00']);
        Notification::assertSentTo($person, MeetingChanged::class);
        $this->get(route('attendance.index'))->assertOk()->assertSee('Konfirmasi ulang');
        $this->post(route('attendance.store', $a), ['choice' => 'will_attend', 'schedule_version' => 1])->assertSessionHasErrors('schedule_version');
        app(MeetingService::class)->delete($b);
        $this->get(route('attendance.index'))->assertOk()->assertDontSee('Jadwal bertumpuk')->assertSee('Konfirmasi ulang');
        $this->assertDatabaseHas('attendance_snapshots', ['meeting_id' => $a->id, 'schedule_version' => 1, 'confirmed_count' => 1]);
    }

    public function test_recap_compares_twenty_distinct_people_and_marks_a_meeting_created_after_deadline(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:00', 'UTC'));
        $people = User::factory()->count(20)->create();
        $meeting = $this->meeting('09:00', '10:00', ...$people->all());
        foreach ($people->take(12) as $person) {
            $this->actingAs($person);
            $this->choose($meeting);
        }
        foreach ($people->slice(12, 3) as $person) {
            Invitation::where('email', $person->email)->update(['status' => 'declined', 'responded_at' => now()]);
        }

        $this->travelTo($meeting->start_time->subDay()->addSecond());
        $this->actingAs($people->last());
        $this->choose($meeting);
        $this->assertDatabaseHas('attendance_snapshots', [
            'meeting_id' => $meeting->id, 'confirmed_count' => 12, 'uncertain_count' => 5, 'not_attending_count' => 3,
        ]);
        $this->actingAs($meeting->organizer)->get(route('attendance.summary', $meeting))
            ->assertOk()->assertViewHas('current', [
                'confirmed_count' => 13, 'uncertain_count' => 4, 'not_attending_count' => 3, 'unchosen_count' => 0,
            ]);

        $lateMeeting = $this->meeting('08:00', '09:00', $people->last());
        $this->actingAs($lateMeeting->organizer)->get(route('attendance.summary', $lateMeeting))
            ->assertOk()->assertSee('Rekap H-1 tidak tersedia');
        $this->assertDatabaseMissing('attendance_snapshots', ['meeting_id' => $lateMeeting->id]);
    }

    private function meeting(string $start, string $end, User ...$invitees): Meeting
    {
        $meeting = Meeting::factory()->create([
            'start_time' => "2026-12-03 $start:00", 'end_time' => "2026-12-03 $end:00", 'mode' => 'offline',
        ]);

        foreach ($invitees as $person) {
            (new Invitation)->forceFill([
                'meeting_id' => $meeting->id, 'email' => $person->email, 'status' => 'pending',
                'token_hash' => hash('sha256', $meeting->id.':'.$person->id), 'expires_at' => $meeting->end_time->addDay(),
            ])->save();
        }

        return $meeting;
    }

    private function choose(Meeting $meeting): void
    {
        $this->post(route('attendance.store', $meeting), ['choice' => 'will_attend', 'schedule_version' => $meeting->schedule_version])
            ->assertRedirect(route('attendance.index'))->assertSessionHasNoErrors();
    }
}
