<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Participation\Jobs\SendInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_matching_verified_recipient_can_accept_once(): void
    {
        Queue::fake();
        $meeting = Meeting::factory()->create();
        $user = User::factory()->unverified()->create();
        $url = "/meetings/{$meeting->id}/participants";
        $this->actingAs($meeting->organizer)->post($url, ['email' => strtoupper($user->email)])->assertRedirect();
        $this->post($url, ['email' => $user->email])->assertSessionHasErrors('email');
        $delivery = Queue::pushed(SendInvitation::class)->sole();
        $this->post('/invitation/open', ['token' => $delivery->token])->assertRedirect();
        $this->assertDatabaseCount('meeting_participants', 0);
        $this->post('/invitation/accepted', ['invitation_id' => DB::table('invitations')->value('id')])->assertForbidden();
        $this->actingAs($user)->post('/invitation/accepted', ['invitation_id' => DB::table('invitations')->value('id')])->assertRedirectToRoute('verification.notice');
        $user->markEmailAsVerified();
        $this->post('/invitation/accepted', ['invitation_id' => DB::table('invitations')->value('id')])->assertRedirect();
        $this->post('/invitation/accepted', ['invitation_id' => DB::table('invitations')->value('id')])->assertRedirect();
        $this->assertDatabaseCount('meeting_participants', 1);
        $this->assertDatabaseHas('invitations', ['status' => 'accepted', 'user_id' => $user->id]);
    }

    public function test_rotated_revoked_and_expired_links_cannot_change_membership(): void
    {
        Queue::fake();
        $meeting = Meeting::factory()->create();
        $url = "/meetings/{$meeting->id}/participants";
        $this->actingAs($meeting->organizer)->post($url, ['email' => 'new@example.test'])->assertRedirect();
        $id = DB::table('invitations')->value('id');
        $old = Queue::pushed(SendInvitation::class)->sole()->token;
        $manage = "/meetings/{$meeting->id}/invitations/$id";
        $this->post("$manage/resend")->assertSessionHasErrors();
        $this->travel(61)->seconds();
        $this->post("$manage/resend")->assertRedirect();
        $this->post('/invitation/open', ['token' => $old])->assertGone();
        $new = Queue::pushed(SendInvitation::class)->last()->token;
        $this->delete($manage)->assertRedirect();
        $this->post('/invitation/open', ['token' => $new])->assertGone();
        $this->post($url, ['email' => 'new@example.test'])->assertRedirect();
        $this->travel(7)->days();
        $this->post('/invitation/open', ['token' => Queue::pushed(SendInvitation::class)->last()->token])->assertGone();
        $this->artisan('rapatin:expire-invitations')->assertSuccessful();
        $this->assertDatabaseHas('invitations', ['status' => 'expired']);
        $this->assertDatabaseCount('meeting_participants', 0);
    }

    public function test_delivery_failure_resend_and_idempotent_decline(): void
    {
        Queue::fake();
        $meeting = Meeting::factory()->create();
        $recipient = User::factory()->create();
        $base = "/meetings/{$meeting->id}";
        $this->actingAs($meeting->organizer)->post($base.'/participants', ['email' => $recipient->email])->assertRedirect();
        $job = Queue::pushed(SendInvitation::class)->sole();
        config(['mail.default' => 'smtp']);
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $job->handle();
        $this->assertDatabaseHas('invitation_deliveries', ['status' => 'failed']);
        $this->assertDatabaseHas('invitations', ['status' => 'pending']);
        $id = DB::table('invitations')->value('id');
        $this->actingAs($recipient)->post("$base/invitations/$id/resend")->assertForbidden();
        $this->actingAs($meeting->organizer);
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->travel(61)->seconds();
            $this->post("$base/invitations/$id/resend")->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->travel(61)->seconds();
        $this->post("$base/invitations/$id/resend")->assertSessionHasErrors('email');
        $job = Queue::pushed(SendInvitation::class)->last();
        Mail::shouldReceive('raw')->once();
        $job->handle();
        $this->assertDatabaseHas('invitation_deliveries', ['status' => 'sent']);
        $this->assertDatabaseCount('invitations', 1);
        $this->actingAs($recipient)->post('/invitation/open', ['token' => $job->token])->assertRedirect();
        for ($i = 0; $i < 2; $i++) {
            $this->post('/invitation/declined', ['invitation_id' => $id])->assertRedirect();
        }
        $this->assertDatabaseHas('invitations', ['status' => 'declined', 'user_id' => $recipient->id]);
        $this->assertDatabaseCount('meeting_participants', 0);
    }
}
