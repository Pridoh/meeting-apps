<?php

namespace App\Modules\Notes\Services;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Notes\Models\MeetingNote;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class NotesService
{
    // Semua penulis mengunci meeting dahulu, termasuk saat baris meeting_notes belum ada.
    public function change(Meeting $meeting, int $revision, Closure $change, bool $finalizing = false): void
    {
        DB::transaction(function () use ($meeting, $revision, $change, $finalizing) {
            $meeting = Meeting::query()->lockForUpdate()->findOrFail($meeting->id);
            $note = MeetingNote::where('meeting_id', $meeting->id)->first() ?? new MeetingNote;
            if ($finalizing && $note->is_finalized) {
                return;
            }
            abort_if($note->is_finalized || (int) $note->revision !== $revision, 409, 'Notulen telah berubah. Muat ulang halaman.');
            $note->meeting_id = $meeting->id;
            $change($note, $meeting);
            $note->revision = $revision + 1;
            $note->save();
        });
    }

    public function checkAssignees(Meeting $meeting, array $review): void
    {
        $members = $meeting->participants()->pluck('users.id', 'email');
        foreach ($review['action_items'] as $item) {
            if ($item['assignee'] !== null && ! $members->has($item['assignee'])) {
                throw ValidationException::withMessages(['assignee' => 'Pilih penanggung jawab dari anggota meeting ini.']);
            }
        }
    }

    public function finalize(Meeting $meeting, int $revision, int $actor): void
    {
        $this->change($meeting, $revision, function (MeetingNote $note, Meeting $meeting) use ($actor) {
            $review = $note->reviewed_data;
            if (! $review) {
                throw ValidationException::withMessages(['review' => 'Simpan hasil review terlebih dahulu.']);
            }
            $this->checkAssignees($meeting, $review);
            $members = $meeting->participants()->pluck('users.id', 'email');
            $metadata = ['meeting_id' => $meeting->id, 'created_at' => now(), 'updated_at' => now()];
            foreach ($review['decisions'] as $decision) {
                DB::table('decisions')->insert($metadata + ['description' => $decision]);
            }
            foreach ($review['action_items'] as $item) {
                DB::table('action_items')->insert($metadata + [
                    'description' => $item['description'], 'deadline' => $item['deadline'],
                    'status' => 'pending', 'assignee_user_id' => $members->get($item['assignee']),
                ]);
            }
            $note->is_finalized = true;
            $note->finalized_at = now();
            $note->finalized_by = $actor;
            DB::afterCommit(fn () => Log::info('Meeting notes finalized', [
                'meeting_id' => $meeting->id,
                'actor_id' => $actor,
            ]));
        }, finalizing: true);
    }
}
