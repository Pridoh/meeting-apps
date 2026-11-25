<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeetingParticipationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_updates_own_task_and_organizer_assigns_only_current_members(): void
    {
        $this->withoutVite();
        [$organizer, $member, $otherMember, $outsideMember] = User::factory()->count(4)->create()->all();
        $meeting = Meeting::factory()->for($organizer, 'organizer')->create();
        $otherMeeting = Meeting::factory()->create(['start_time' => now()->addDay()]);
        $meeting->participants()->attach([$member->id, $otherMember->id]);
        $otherMeeting->participants()->attach($outsideMember);
        $ownTask = ActionItem::factory()->for($meeting)->for($member, 'assignee')->create(['deadline' => '2026-12-10']);
        $otherTask = ActionItem::factory()->for($meeting)->for($otherMember, 'assignee')->create();
        $foreignTask = ActionItem::factory()->for($otherMeeting)->for($outsideMember, 'assignee')->create();
        DB::table('meeting_notes')->insert([
            'meeting_id' => $meeting->id,
            'raw_draft' => 'Draf evaluasi',
            'reviewed_data' => json_encode(['summary' => 'Evaluasi final']),
            'is_finalized' => true,
            'finalized_by' => $organizer->id,
            'finalized_at' => now(),
        ]);
        $frozenNote = DB::table('meeting_notes')->where('meeting_id', $meeting->id)->first();
        $taskUrl = '/meetings/'.$meeting->id.'/action-items/';

        $this->actingAs($member)->get('/dashboard')->assertOk()
            ->assertViewHas('participantTaskCount', 1)
            ->assertViewHas('completedTaskCount', 0)
            ->assertViewHas('upcomingMeetings', fn ($meetings): bool => $meetings->pluck('id')->all() === [$meeting->id]);
        $this->patch($taskUrl.$ownTask->id, ['status' => 'completed'])->assertRedirect();
        $this->assertSame('completed', $ownTask->fresh()->status);
        $this->patch($taskUrl.$otherTask->id, ['status' => 'completed'])->assertForbidden();
        $this->patch($taskUrl.$ownTask->id, [
            'status' => 'pending',
            'assignee_user_id' => $otherMember->id,
        ])->assertForbidden();

        $this->actingAs($organizer)->patch($taskUrl.$foreignTask->id, ['status' => 'completed'])->assertNotFound();
        $this->patch($taskUrl.$otherTask->id, [
            'status' => 'pending',
            'assignee_user_id' => $outsideMember->id,
        ])->assertSessionHasErrors('assignee_user_id');
        $this->assertSame($otherMember->id, $otherTask->fresh()->assignee_user_id);

        $this->patch($taskUrl.$otherTask->id, [
            'status' => 'in_progress',
            'assignee_user_id' => $member->id,
        ])->assertRedirect();
        $this->assertSame($member->id, $otherTask->fresh()->assignee_user_id);
        $this->assertEquals($frozenNote, DB::table('meeting_notes')->where('meeting_id', $meeting->id)->first());
    }

    public function test_confirmed_member_removal_revokes_access_and_clears_only_active_tasks(): void
    {
        $organizer = User::factory()->create();
        $member = User::factory()->create(['email' => 'peserta@example.test']);
        $meeting = Meeting::factory()->for($organizer, 'organizer')->create();
        $baseUrl = '/meetings/'.$meeting->id;
        $active = ActionItem::factory()->for($meeting)->for($member, 'assignee')->create();
        $completed = ActionItem::factory()->for($meeting)->for($member, 'assignee')->create(['status' => 'completed']);

        // Keanggotaan lama dari Fase 1 tetap dapat dikelola.
        $meeting->participants()->attach($member);
        $this->actingAs($organizer);
        $this->assertDatabaseCount('meeting_participants', 1);

        $this->delete($baseUrl.'/participants/'.$member->id)->assertSessionHasErrors('confirmed');
        $this->assertSame($member->id, $active->fresh()->assignee_user_id);
        $this->delete($baseUrl.'/participants/'.$member->id, ['confirmed' => '1'])->assertRedirect($baseUrl);

        $this->assertDatabaseCount('meeting_participants', 0);
        $this->assertNull($active->fresh()->assignee_user_id);
        $this->assertSame($member->id, $completed->fresh()->assignee_user_id);
        $this->actingAs($member)->get($baseUrl)->assertForbidden();
        $this->patch($baseUrl.'/action-items/'.$completed->id, ['status' => 'pending'])->assertForbidden();
    }

    public function test_meeting_details_and_mutations_deny_outsiders_and_restrict_members(): void
    {
        $this->withoutVite();
        [$organizer, $member, $otherOrganizer] = User::factory()->count(3)->create()->all();
        $meeting = Meeting::factory()->for($organizer, 'organizer')->create();
        $otherMeeting = Meeting::factory()->for($otherOrganizer, 'organizer')->create();
        $meeting->participants()->attach($member);
        $meetingUrl = '/meetings/'.$meeting->id;

        $this->actingAs($member);
        $this->get('/meetings/'.$otherMeeting->id)->assertForbidden();
        $this->get($meetingUrl.'/edit')->assertForbidden();
        $this->post($meetingUrl.'/agendas', ['title' => 'Agenda asing'])->assertForbidden();
        $this->post($meetingUrl.'/participants', ['email' => $otherOrganizer->email])->assertForbidden();

        $this->actingAs($otherOrganizer)->get($meetingUrl)->assertForbidden();
        $this->patch($meetingUrl.'/status', ['status' => 'ongoing'])->assertForbidden();
        $this->delete($meetingUrl)->assertForbidden();
        $this->assertNotSoftDeleted($meeting);
    }

    public function test_member_can_open_their_meeting_and_only_sees_their_tasks(): void
    {
        $this->withoutVite();
        [$member, $otherMember] = User::factory()->count(2)->create()->all();
        $meeting = Meeting::factory()->create();
        $meeting->participants()->attach([$member->id, $otherMember->id]);
        $ownTask = ActionItem::factory()->for($meeting)->for($member, 'assignee')->create();
        ActionItem::factory()->for($meeting)->for($otherMember, 'assignee')->create();

        $this->actingAs($member)->get('/action-items')->assertOk()
            ->assertViewHas('actionItems', fn ($items): bool => $items->pluck('id')->all() === [$ownTask->id]);
        $this->get('/meetings/'.$meeting->id)->assertOk()
            ->assertViewHas('actionItems', fn ($items): bool => $items->pluck('id')->all() === [$ownTask->id]);
    }
}
