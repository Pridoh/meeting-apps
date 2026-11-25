<?php

namespace App\Modules\Rooms\Services;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Rooms\Models\Room;
use App\Modules\Rooms\Models\RoomRequest;
use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class RoomBooking
{
    public function latest(Meeting $meeting): ?RoomRequest
    {
        return RoomRequest::where('meeting_id', $meeting->id)->latest('id')->first();
    }

    public function overlaps(string $table, DateTimeInterface $start, DateTimeInterface $end): Builder
    {
        return DB::table($table)->where('start_time', '<', $end)->where('end_time', '>', $start);
    }

    public function roomsForReviewer(User $reviewer): Builder
    {
        return DB::table('room_reviewers')->where('user_id', $reviewer->id)->select('room_id');
    }

    /**
     * @param  array{organization_id: int, room_id: int, people_count: int, purpose?: string|null, intent: string, version: int, schedule_version: int, attachments?: list<UploadedFile>}  $data
     */
    public function save(Meeting $meeting, array $data): RoomRequest
    {
        $storedPaths = [];
        try {
            return DB::transaction(function () use ($meeting, $data, &$storedPaths): RoomRequest {
                $meeting = Meeting::lockForUpdate()->findOrFail($meeting->id);
                abort_unless($meeting->mode === 'offline' && $meeting->status === 'scheduled', 409);
                $room = Room::lockForUpdate()->findOrFail($data['room_id']);
                $organizationId = $room->organizationId();
                abort_unless($organizationId === (int) $data['organization_id'] && DB::table('organization_memberships')
                    ->where('organization_id', $organizationId)->where('user_id', $meeting->user_id)->exists(), 404);
                $this->check($room->isAvailableInCatalog(), 'room_id', 'Ruang ini sudah tidak aktif.');
                $this->check($meeting->schedule_version === (int) $data['schedule_version'], 'schedule_version', 'Jadwal meeting berubah. Buka kembali formulir pengajuan.');

                $previous = $this->latest($meeting);
                $active = $previous && ! in_array($previous->status, ['withdrawn', 'superseded'], true) ? $previous : null;
                $this->check(($active?->version ?? 0) === (int) $data['version'], 'version', 'Versi pengajuan berubah. Buka kembali formulir.');
                $this->check(! $active || in_array($active->status, RoomRequest::EDITABLE, true), 'room_id', 'Tarik pengajuan aktif sebelum memilih ruang lain.');

                if ($active) {
                    $this->check($active->room->organizationId() === $organizationId, 'room_id', 'Tarik pengajuan untuk organisasi sebelumnya sebelum berpindah.');
                }

                $files = $data['attachments'] ?? [];
                $existing = $active ? $this->attachments($active)->get() : collect();
                $submitting = $data['intent'] === 'submit';
                if ($submitting) {
                    $this->check($files !== [] || $existing->isNotEmpty(), 'attachments', 'Unggah paling sedikit satu lampiran.');
                    $this->validateSlot($room, $meeting, (int) $data['people_count']);
                }

                $booking = $active ?? new RoomRequest;
                $booking->forceFill([
                    'meeting_id' => $meeting->id, 'requester_id' => $meeting->user_id, 'room_id' => $room->id,
                    'start_time' => $meeting->start_time, 'end_time' => $meeting->end_time,
                    'purpose' => $data['purpose'] ?? '', 'people_count' => $data['people_count'],
                    'version' => ($active?->version ?? 0) + 1,
                    'status' => $submitting ? 'submitted' : 'draft',
                    'submitted_at' => $submitting ? now() : null,
                ])->save();

                $attachments = [];
                foreach ($files as $file) {
                    $path = $file->store('room-requests', 'local');
                    if ($path === false) {
                        throw new RuntimeException('Lampiran pengajuan gagal disimpan.');
                    }
                    $storedPaths[] = $path;
                    $attachments[] = [
                        'path' => $path, 'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
                    ];
                }

                if ($files === []) {
                    $attachments = $existing->map(fn (object $file) => [
                        'path' => $file->path, 'original_name' => $file->original_name,
                        'mime_type' => $file->mime_type, 'size' => $file->size,
                    ])->all();
                }

                foreach ($attachments as $attachment) {
                    DB::table('room_request_attachments')->insert([
                        ...$attachment, 'room_request_id' => $booking->id, 'request_version' => $booking->version,
                    ]);
                }
                $meeting->forceFill(['room_status' => $submitting ? 'pending' : 'none'])->save();

                return $booking;
            });
        } catch (Throwable $error) {
            Storage::disk('local')->delete($storedPaths);
            throw $error;
        }
    }

    public function decide(RoomRequest $booking, User $reviewer, int $version, string $result, string $reason): void
    {
        DB::transaction(function () use ($booking, $reviewer, $version, $result, $reason): void {
            $meeting = Meeting::lockForUpdate()->findOrFail($booking->meeting_id);
            $current = RoomRequest::lockForUpdate()->findOrFail($booking->id);
            $room = Room::lockForUpdate()->findOrFail($current->room_id);
            Gate::forUser($reviewer)->authorize('review', $current);
            $this->check($current->version === $version && $current->status === 'submitted', 'version', 'Versi ini sudah berubah atau sudah diputuskan. Muat ulang pengajuan.');

            if ($result === 'approved') {
                $this->check($meeting->status === 'scheduled' && $meeting->mode === 'offline'
                    && $meeting->start_time->eq($current->start_time) && $meeting->end_time->eq($current->end_time),
                    'room_id', 'Jadwal meeting berubah. Organizer perlu mengajukan ulang.');
                $this->validateSlot($room, $meeting, $current->people_count);
                DB::table('room_reservations')->insert([
                    'room_id' => $room->id, 'room_request_id' => $current->id, 'meeting_id' => $meeting->id,
                    'start_time' => $meeting->start_time, 'end_time' => $meeting->end_time, 'status' => 'active',
                ]);
            }
            DB::table('room_request_decisions')->insert([
                'room_request_id' => $current->id, 'reviewer_id' => $reviewer->id,
                'request_version' => $version, 'result' => $result, 'reason' => $reason, 'decided_at' => now(),
            ]);
            $current->forceFill(['status' => $result])->save();
            $meeting->forceFill([
                'room_id' => $result === 'approved' ? $room->id : null,
                'room_status' => match ($result) {
                    'approved' => 'confirmed',
                    'needs_revision' => 'pending',
                    default => 'none',
                },
            ])->save();
        }, attempts: 3);
    }

    public function withdraw(RoomRequest $booking, int $version, bool $confirmed): void
    {
        DB::transaction(function () use ($booking, $version, $confirmed): void {
            $meeting = Meeting::lockForUpdate()->findOrFail($booking->meeting_id);
            $current = RoomRequest::lockForUpdate()->findOrFail($booking->id);
            $this->check($current->version === $version && $this->latest($meeting)?->id === $current->id
                && in_array($current->status, ['draft', 'submitted', 'needs_revision', 'approved', 'rejected'], true),
                'version', 'Pengajuan ini sudah berubah. Muat ulang pengajuan.');
            $this->invalidate($meeting, $confirmed, $current->status === 'approved' ? 'superseded' : 'withdrawn');
            $meeting->save();
        }, attempts: 3);
    }

    /** The caller must hold the meeting row lock and save the updated meeting in the same transaction. */
    public function invalidate(Meeting $meeting, bool $confirmed, string $status = 'superseded'): void
    {
        $booking = $this->latest($meeting);
        if (! $booking || in_array($booking->status, ['withdrawn', 'superseded'], true)) {
            return;
        }

        $this->check($booking->status !== 'approved' || $confirmed, 'confirm_room_release',
            'Konfirmasi bahwa reservasi lama akan dilepas dan ruang perlu diajukan ulang.');
        if ($booking->status === 'approved') {
            Room::lockForUpdate()->findOrFail($booking->room_id);
            DB::table('room_reservations')->where('room_request_id', $booking->id)->where('status', 'active')
                ->update(['status' => 'released', 'released_at' => now()]);
        }
        $booking->forceFill(['status' => $status])->save();
        $meeting->forceFill(['room_id' => null, 'room_status' => 'none']);
    }

    public function attachments(RoomRequest $booking): Builder
    {
        return DB::table('room_request_attachments')->where('room_request_id', $booking->id)->where('request_version', $booking->version);
    }

    private function validateSlot(Room $room, Meeting $meeting, int $peopleCount): void
    {
        $this->check($room->building_id === null ? $room->is_active : $room->isAvailableInCatalog(), 'room_id', 'Ruang ini sudah tidak aktif.');
        $this->check($peopleCount <= $room->capacity, 'people_count', 'Ruang kurang '.max(0, $peopleCount - $room->capacity).' kursi.');
        $this->check($meeting->start_time->isFuture(), 'room_id', 'Waktu mulai meeting harus masih mendatang.');
        $reserved = $this->overlaps('room_reservations', $meeting->start_time, $meeting->end_time)
            ->where('room_id', $room->id)->where('status', 'active')->lockForUpdate()->first();
        $this->check(! $reserved, 'room_id', 'Slot sudah terisi. Pilih ruang lain atau ubah jadwal meeting.');
    }

    private function check(bool $valid, string $field, string $message): void
    {
        if (! $valid) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
