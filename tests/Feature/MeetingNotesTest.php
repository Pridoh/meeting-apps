<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MeetingNotesTest extends TestCase
{
    use RefreshDatabase;

    private string $url;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $owner = User::factory()->create();
        $this->member = User::factory()->create();
        $id = DB::table('meetings')->insertGetId([
            'user_id' => $owner->id, 'title' => 'Rencana', 'mode' => 'offline',
            'start_time' => now(), 'end_time' => now()->addHour(),
        ]);
        DB::table('meeting_participants')->insert(['meeting_id' => $id, 'user_id' => $this->member->id]);
        $this->url = "/meetings/$id/notes";
        $this->actingAs($owner);
        config(['services.notes.provider' => 'fake']);
    }

    public function test_review_is_required_and_finalization_is_idempotent(): void
    {
        $this->post($this->url.'/finalize', ['confirm' => 1, 'revision' => 0])->assertSessionHasErrors();
        $this->put($this->url.'/draft', ['revision' => 0, 'draft_file' => UploadedFile::fake()->createWithContent('rapat.txt', 'Catat keputusan rapat.')])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $this->post($this->url.'/generate', ['revision' => 1])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $review = ['meeting_summary' => 'Sudah ditinjau', 'decisions' => ['Disepakati'], 'key_discussion_points' => [],
            'action_items' => [['description' => 'Kirim laporan', 'assignee' => $this->member->email, 'status' => 'pending', 'deadline' => null]]];
        $this->put($this->url.'/review', ['revision' => 2, 'review' => $review])->assertRedirect($this->url)->assertSessionHasNoErrors();
        for ($i = 0; $i < 2; $i++) {
            $this->post($this->url.'/finalize', ['revision' => 3, 'confirm' => 1])->assertRedirect($this->url)->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('decisions', 1);
        $this->assertDatabaseCount('action_items', 1);
        $this->assertDatabaseHas('action_items', ['assignee_user_id' => $this->member->id]);
        $this->put($this->url.'/draft', ['revision' => 4, 'raw_draft' => 'changed'])->assertConflict();
        $this->actingAs($this->member)->get($this->url)->assertOk()->assertSee('Sudah ditinjau')->assertDontSee('Catat keputusan rapat.');
        $this->post($this->url.'/generate', ['revision' => 4])->assertForbidden();
    }

    public function test_invalid_input_and_provider_failures_preserve_saved_review(): void
    {
        $this->put($this->url.'/draft', ['revision' => 0, 'raw_draft' => '   '])->assertSessionHasErrors();
        $this->put($this->url.'/draft', ['revision' => 0, 'raw_draft' => 'Catatan awal'])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $this->post($this->url.'/generate', ['revision' => 1])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $review = ['meeting_summary' => 'Tersimpan', 'key_discussion_points' => [], 'decisions' => [], 'action_items' => []];
        $this->put($this->url.'/review', ['revision' => 2, 'review' => $review])->assertRedirect($this->url)->assertSessionHasNoErrors();
        config(['services.notes.provider' => 'gemini', 'services.notes.key' => 'test', 'services.notes.model' => 'test']);
        Http::fakeSequence()->push([])->push([])->pushFailedConnection();
        $this->post($this->url.'/generate', ['revision' => 3])->assertSessionHasErrors();
        $this->post($this->url.'/generate', ['revision' => 3])->assertSessionHasErrors();
        Http::assertSentCount(3);
        $this->assertDatabaseHas('meeting_notes', ['revision' => 3, 'raw_draft' => 'Catatan awal']);
        $this->assertSame('Tersimpan', json_decode(DB::table('meeting_notes')->value('reviewed_data'), true)['meeting_summary']);
        $this->actingAs($this->member)->get($this->url)->assertForbidden();
    }

    public function test_upload_limits_and_stale_forms_cannot_replace_newer_notes(): void
    {
        $this->put($this->url.'/draft', ['revision' => 0, 'draft_file' => UploadedFile::fake()->createWithContent('agenda.csv', 'wrong extension')])
            ->assertSessionHasErrors('draft_file');
        $this->put($this->url.'/draft', ['revision' => 0, 'draft_file' => UploadedFile::fake()->create('agenda.txt', 1025, 'text/plain')])
            ->assertSessionHasErrors('draft_file');
        $this->put($this->url.'/draft', ['revision' => 0, 'raw_draft' => 'Pertama'])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $this->post($this->url.'/generate', ['revision' => 1])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $this->put($this->url.'/draft', ['revision' => 2, 'raw_draft' => 'Revisi'])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $this->post($this->url.'/generate', ['revision' => 1])->assertConflict();
        $this->assertDatabaseHas('meeting_notes', ['raw_draft' => 'Revisi', 'reviewed_data' => null, 'ai_raw_json' => null]);
    }

    public function test_gemini_output_with_unmatched_assignee_requires_manual_attention(): void
    {
        config(['services.notes.provider' => 'gemini', 'services.notes.key' => 'test', 'services.notes.model' => 'test']);
        $data = ['meeting_summary' => 'Pembagian tugas', 'decisions' => [], 'key_discussion_points' => ['Jadwal'],
            'action_items' => [['description' => 'Kirim laporan', 'status' => 'pending', 'assignee' => 'outside@example.test', 'deadline' => null]]];
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($data)]]]]]])]);
        $this->put($this->url.'/draft', ['revision' => 0, 'raw_draft' => 'Rapat tim'])->assertRedirect($this->url)->assertSessionHasNoErrors();
        $this->post($this->url.'/generate', ['revision' => 1])->assertRedirect($this->url)->assertSessionHasNoErrors();
        Http::assertSent(function ($request) {
            $context = json_decode($request['contents'][0]['parts'][0]['text'], true);

            return array_keys($context) === ['draft', 'title', 'description', 'date', 'agenda', 'participants']
                && $context['participants'] === [$this->member->only('name', 'email')]
                && $request->hasHeader('x-goog-api-key', 'test');
        });
        $this->get($this->url)->assertOk()->assertSee('Penanggung jawab yang tidak cocok')->assertDontSee('outside@example.test');
        $this->put($this->url.'/review', ['revision' => 2, 'review' => $data])->assertSessionHasErrors('assignee');
        $data['action_items'][0]['assignee'] = $this->member->email;
        $this->put($this->url.'/review', ['revision' => 2, 'review' => $data])->assertRedirect($this->url)->assertSessionHasNoErrors();
        DB::table('meeting_participants')->where('user_id', $this->member->id)->delete();
        $this->post($this->url.'/finalize', ['revision' => 3, 'confirm' => 1])->assertSessionHasErrors('assignee');
        $this->assertDatabaseCount('action_items', 0);
        $this->assertDatabaseHas('meeting_notes', ['is_finalized' => false]);
    }
}
