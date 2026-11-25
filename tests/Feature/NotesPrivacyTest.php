<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Notes\Models\MeetingNote;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Tests\TestCase;

class NotesPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_notes_show_only_the_viewing_participants_own_action_items(): void
    {
        $this->withoutVite();
        $meeting = Meeting::factory()->create();
        $participant = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $meeting->participants()->attach([$participant->id, $otherParticipant->id]);
        $note = new MeetingNote;
        $note->meeting_id = $meeting->id;
        $note->raw_draft = 'Draf privat';
        $note->reviewed_data = [
            'meeting_summary' => 'Ringkasan untuk anggota',
            'key_discussion_points' => [],
            'decisions' => [],
            'action_items' => [
                ['description' => 'Tugas saya', 'assignee' => $participant->email, 'deadline' => null, 'status' => 'pending'],
                ['description' => 'Tugas peserta lain', 'assignee' => $otherParticipant->email, 'deadline' => '2030-03-15', 'status' => 'pending'],
                ['description' => 'Tugas tanpa penanggung jawab', 'assignee' => null, 'deadline' => null, 'status' => 'pending'],
            ],
        ];
        $note->is_finalized = true;
        $note->finalized_at = now();
        $note->finalized_by = $meeting->user_id;
        $note->save();

        $this->actingAs($participant)->get(route('notes.show', $meeting))
            ->assertOk()
            ->assertSee('Ringkasan untuk anggota')
            ->assertSee('Tugas saya')
            ->assertDontSee('Tugas peserta lain')
            ->assertDontSee($otherParticipant->email)
            ->assertDontSee('2030-03-15')
            ->assertDontSee('Tugas tanpa penanggung jawab')
            ->assertDontSee('Draf privat');

        $this->actingAs($meeting->organizer)->get(route('notes.show', $meeting))
            ->assertOk()
            ->assertSee('Tugas saya')
            ->assertSee('Tugas peserta lain')
            ->assertSee('Tugas tanpa penanggung jawab');
    }

    public function test_failed_note_write_reports_an_exception_without_draft_text(): void
    {
        Exceptions::fake();
        $meeting = Meeting::factory()->create();
        $originalDraft = 'Catatan tersimpan';
        $privateDraft = 'Catatan privat untuk percobaan yang gagal';
        $this->actingAs($meeting->organizer)
            ->put(route('notes.draft', $meeting), ['revision' => 0, 'raw_draft' => $originalDraft])
            ->assertRedirectToRoute('notes.show', $meeting);

        MeetingNote::saving(function (MeetingNote $note): void {
            // Duplicate meeting_id forces the database to reject this write.
            DB::table('meeting_notes')->insert([
                'meeting_id' => $note->meeting_id,
                'raw_draft' => $note->raw_draft,
            ]);
        });

        $this->put(route('notes.draft', $meeting), ['revision' => 1, 'raw_draft' => $privateDraft])
            ->assertInternalServerError();

        Exceptions::assertReported(UniqueConstraintViolationException::class);
        Exceptions::assertNotReported(fn (UniqueConstraintViolationException $exception): bool => str_contains($exception->getMessage(), $privateDraft));
        $this->assertDatabaseHas('meeting_notes', ['meeting_id' => $meeting->id, 'raw_draft' => $originalDraft, 'revision' => 1]);
    }
}
