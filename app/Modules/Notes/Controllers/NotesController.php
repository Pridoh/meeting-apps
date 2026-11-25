<?php

namespace App\Modules\Notes\Controllers;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Notes\Models\MeetingNote;
use App\Modules\Notes\Services\AiService;
use App\Modules\Notes\Services\NotesContent;
use App\Modules\Notes\Services\NotesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NotesController
{
    public function __construct(private NotesService $notes) {}

    public function show(Request $request, Meeting $meeting): View
    {
        $note = MeetingNote::where('meeting_id', $meeting->id)->first();
        abort_unless($note?->is_finalized || $request->user()->can('update', $meeting), 403);
        $review = $note?->reviewed_data ?? $note?->ai_raw_json;
        $unknownAssignee = false;
        if ($note?->is_finalized && $request->user()->cannot('update', $meeting)) {
            $review['action_items'] = array_filter($review['action_items'], fn (array $item) => $item['assignee'] === $request->user()->email);
        }
        if ($review && ! $note->is_finalized) {
            $members = $meeting->participants->pluck('email');
            foreach ($review['action_items'] as &$item) {
                if ($item['assignee'] !== null && ! $members->containsStrict($item['assignee'])) {
                    $item['assignee'] = null;
                    $unknownAssignee = true;
                }
            }
            unset($item);
        }

        return view('notes.show', compact('meeting', 'note', 'review', 'unknownAssignee'));
    }

    private function revision(Request $request): int
    {
        return (int) $request->validate(['revision' => ['required', 'integer', 'min:0']])['revision'];
    }

    public function draft(Request $request, Meeting $meeting): RedirectResponse
    {
        $revision = $this->revision($request);
        $request->validate([
            'raw_draft' => ['nullable', 'string', 'required_without:draft_file'],
            'draft_file' => ['nullable', 'file', 'extensions:txt', 'mimetypes:text/plain', 'max:1024'],
        ]);
        $text = $request->hasFile('draft_file') ? $request->file('draft_file')->get() : $request->input('raw_draft');
        if (! is_string($text) || trim($text) === '' || strlen($text) > 1048576 || ! mb_check_encoding($text, 'UTF-8') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $text)) {
            throw ValidationException::withMessages(['raw_draft' => 'Gunakan teks UTF-8 yang terisi, maksimal 1 MB.']);
        }
        $this->notes->change($meeting, $revision, function (MeetingNote $note) use ($text) {
            if ($note->raw_draft !== $text) {
                $note->raw_draft = $text;
                $note->ai_raw_json = $note->ai_summary = $note->reviewed_data = null;
            }
        });

        return to_route('notes.show', $meeting);
    }

    public function generate(Request $request, Meeting $meeting, AiService $provider): RedirectResponse
    {
        $revision = $this->revision($request);
        $note = MeetingNote::where('meeting_id', $meeting->id)->first();
        abort_if($note?->is_finalized, 409);
        if (! $note) {
            throw ValidationException::withMessages(['raw_draft' => 'Simpan draf terlebih dahulu.']);
        }
        abort_unless($revision === (int) $note->revision, 409);
        $data = NotesContent::fromProvider($provider, [
            'draft' => $note->raw_draft, 'title' => $meeting->title, 'description' => $meeting->description,
            'date' => $meeting->start_time->timezone($meeting->timezone)->toDateString(),
            'agenda' => $meeting->agendas->map->only(['title', 'description'])->values()->all(),
            'participants' => $meeting->participants->map->only(['name', 'email'])->values()->all(),
        ]);
        $this->notes->change($meeting, $revision, function (MeetingNote $note) use ($data) {
            $note->ai_raw_json = $data;
            $note->ai_summary = $data['meeting_summary'];
            $note->reviewed_data = null;
        });

        return to_route('notes.show', $meeting);
    }

    public function review(Request $request, Meeting $meeting): RedirectResponse
    {
        $revision = $this->revision($request);
        $request->validate(['review' => ['required', 'array']]);
        $review = NotesContent::validate($request->input('review') + ['decisions' => [], 'key_discussion_points' => [], 'action_items' => []]);
        $this->notes->change($meeting, $revision, function (MeetingNote $note, Meeting $meeting) use ($review) {
            abort_unless($note->ai_raw_json, 409);
            $this->notes->checkAssignees($meeting, $review);
            $note->reviewed_data = $review;
        });

        return to_route('notes.show', $meeting);
    }

    public function finalize(Request $request, Meeting $meeting): RedirectResponse
    {
        $request->validate(['confirm' => 'accepted']);
        $this->notes->finalize($meeting, $this->revision($request), $request->user()->id);

        return to_route('notes.show', $meeting);
    }
}
