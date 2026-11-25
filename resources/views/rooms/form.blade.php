@extends('layouts.workspace')

@section('title', 'Pengajuan ruang')

@section('content')
    <header class="workspace-heading">
        <a class="back-link" href="{{ route('meetings.show', $meeting) }}">Kembali ke meeting</a>
        <h1 class="page-title">{{ $booking ? 'Perbarui pengajuan ruang' : 'Ajukan ruang' }}</h1>
        <p class="intro-text wrap-anywhere">{{ $meeting->title }} · {{ $organization->name }}</p>
    </header>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <x-card>
            <form method="POST" enctype="multipart/form-data" action="{{ route('room-requests.store', $meeting) }}" class="grid gap-5">
                @csrf
                <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                <input type="hidden" name="version" value="{{ $booking?->version ?? 0 }}">
                <input type="hidden" name="schedule_version" value="{{ $meeting->schedule_version }}">
                @foreach (['version', 'schedule_version'] as $field)
                    @error($field)<p role="alert" class="form-error">{{ $message }}</p>@enderror
                @endforeach
                <x-form-select id="booking-room" name="room_id" label="Ruang" :error="$errors->first('room_id')" required>
                    <option value="">Pilih ruang</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected($room->id == old('room_id', $selectedRoom?->id))>
                            {{ $buildings[$room->building_id] }} · {{ $room->name }} · {{ $room->capacity }} kursi
                        </option>
                    @endforeach
                </x-form-select>
                <x-input id="booking-people" name="people_count" type="number" min="1" label="Jumlah orang (termasuk panitia)" :value="old('people_count', $booking?->people_count ?? max(1, request()->integer('people_count', $confirmed)))" required />
                <x-form-textarea id="booking-purpose" name="purpose" label="Tujuan penggunaan" :error="$errors->first('purpose')" rows="3" maxlength="3000">{{ old('purpose', $booking?->purpose) }}</x-form-textarea>
                <div>
                    <x-input id="booking-files" name="attachments[]" type="file" label="Lampiran" multiple accept=".pdf,.jpg,.jpeg,.png" aria-describedby="booking-files-help" />
                    <p id="booking-files-help" class="mt-2 text-sm text-muted">Sertakan 1–3 berkas PDF, JPG, atau PNG untuk pengajuan. Maksimal 5 MB per berkas.</p>
                    @forelse ($errors->get('attachments.*') as $messages)
                        <p role="alert" class="form-error">{{ $messages[0] }}</p>
                    @empty
                        @error('attachments')<p role="alert" class="form-error">{{ $message }}</p>@enderror
                    @endforelse
                    @if ($attachments->isNotEmpty())
                        <p class="mt-2 text-sm text-muted">Unggah berkas baru untuk mengganti lampiran versi sebelumnya.</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($attachments as $file)
                                <li><a class="wrap-anywhere text-sm text-terracotta underline" href="{{ route('room-requests.attachment', [$booking, $file->id]) }}">{{ $file->original_name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3 border-t border-line pt-4">
                    <x-button name="intent" value="submit">Kirim pengajuan</x-button>
                    <x-button name="intent" value="draft" variant="secondary">Simpan draf</x-button>
                </div>
            </form>
        </x-card>

        <x-card>
            <h2 class="section-title">Jadwal pengajuan</h2>
            <dl class="mt-3 grid gap-3 text-sm">
                <div><dt class="font-semibold">Pemohon</dt><dd class="text-muted">{{ auth()->user()->name }}</dd></div>
                <div>
                    <dt class="font-semibold">Waktu meeting</dt>
                    <dd class="text-muted">{{ $meeting->start_time->timezone($meeting->timezone)->format('d/m/Y H:i') }}–{{ $meeting->end_time->timezone($meeting->timezone)->format('d/m/Y H:i') }} ({{ $meeting->timezone }})</dd>
                </div>
                <div><dt class="font-semibold">Peserta terkonfirmasi</dt><dd class="text-muted">{{ $confirmed }} orang. Tambahkan kebutuhan panitia pada jumlah pengajuan.</dd></div>
            </dl>
            <a class="back-link mt-4 mb-0" href="{{ route('rooms.index', ['meeting' => $meeting->id, 'organization' => $organization->id]) }}">Periksa ketersediaan ruang →</a>
        </x-card>
    </div>
@endsection
