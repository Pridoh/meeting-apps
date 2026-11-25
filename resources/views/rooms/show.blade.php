@extends('layouts.workspace')

@section('title', 'Detail pengajuan ruang')

@section('content')
    @php
        $owner = auth()->id() === $booking->requester_id;
        $zone = $booking->meeting->timezone;
        $variant = match ($booking->status) {
            'approved' => 'success',
            'submitted', 'needs_revision' => 'warning',
            default => 'neutral',
        };
    @endphp

    <header class="workspace-heading">
        <a class="back-link" href="{{ $owner && ! $booking->meeting->trashed() ? route('meetings.show', $booking->meeting) : route($owner ? 'rooms.index' : 'room-requests.review', ['organization' => $organization?->id]) }}">Kembali</a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="page-title">Pengajuan ruang</h1>
            <x-status-badge :variant="$variant">{{ $booking::STATUSES[$booking->status] }}</x-status-badge>
        </div>
        <p class="intro-text wrap-anywhere">{{ $booking->meeting->title }} · Versi {{ $booking->version }}</p>
    </header>

    @if ($errors->any())
        <ul role="alert" class="mb-5 grid gap-1 rounded-lg border border-danger/35 bg-soft p-4 text-sm font-semibold text-danger">
            @foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
    @endif

    <div class="grid items-start gap-5 lg:grid-cols-2">
        <x-card>
            <h2 class="section-title">Detail peminjaman</h2>
            <dl class="mt-4 grid gap-4 text-sm">
                @if ($organization)
                    <div><dt class="font-semibold">Organisasi</dt><dd class="wrap-anywhere text-muted">{{ $organization->name }}</dd></div>
                    <div><dt class="font-semibold">Gedung</dt><dd class="text-muted">{{ $building->name }}</dd></div>
                @endif
                <div><dt class="font-semibold">Ruang</dt><dd class="text-muted">{{ $booking->room->name }} · {{ $booking->room->location }}</dd></div>
                <div><dt class="font-semibold">Pemohon</dt><dd class="text-muted">{{ $booking->requester->name }}</dd></div>
                <div><dt class="font-semibold">Jadwal</dt><dd class="text-muted">{{ $booking->start_time->timezone($zone)->format('d/m/Y H:i') }}–{{ $booking->end_time->timezone($zone)->format('d/m/Y H:i') }} ({{ $zone }})</dd></div>
                <div><dt class="font-semibold">Jumlah orang</dt><dd class="text-muted">{{ $booking->people_count }} dari {{ $booking->room->capacity }} kursi</dd></div>
                <div><dt class="font-semibold">Tujuan</dt><dd class="wrap-anywhere whitespace-pre-line text-muted">{{ $booking->purpose }}</dd></div>
            </dl>
            <div class="mt-4 border-t border-line pt-4">
                <h3 class="font-semibold">Lampiran versi {{ $booking->version }}</h3>
                <ul class="mt-2 grid gap-2 text-sm">
                    @forelse ($attachments as $file)
                        <li><a class="wrap-anywhere text-terracotta underline" href="{{ route('room-requests.attachment', [$booking, $file->id]) }}">{{ $file->original_name }}</a></li>
                    @empty
                        <li class="text-muted">Belum ada lampiran.</li>
                    @endforelse
                </ul>
            </div>
        </x-card>

        <div class="grid gap-5">
            @if ($owner && $booking->status === 'submitted' && ! $hasReviewer)
                <p class="rounded-lg border border-line bg-cream p-4 text-sm text-terracotta">Peninjau belum tersedia. Pengajuan tetap menunggu.</p>
            @endif

            @if ($decisions->isNotEmpty())
                <x-card>
                    <h2 class="section-title">Riwayat keputusan</h2>
                    <ul class="mt-3 divide-y divide-line">
                        @foreach ($decisions as $decision)
                            <li class="py-3 text-sm">
                                <p class="font-semibold">Versi {{ $decision->request_version }} · {{ $booking::STATUSES[$decision->result] }}</p>
                                <p class="mt-1 wrap-anywhere whitespace-pre-line text-muted">{{ $decision->reason }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif

            @if ($owner && ! $booking->meeting->trashed() && $booking->meeting->status === 'scheduled' && $booking->meeting->mode === 'offline')
                @if (in_array($booking->status, [...$booking::EDITABLE, 'withdrawn', 'superseded'], true))
                    <div>
                        <a class="button button--primary" href="{{ route('room-requests.form', $booking->meeting) }}">
                            {{ in_array($booking->status, $booking::EDITABLE, true) ? 'Perbarui pengajuan' : 'Buat pengajuan baru' }}
                        </a>
                    </div>
                @endif
                @if (in_array($booking->status, ['draft', 'submitted', 'needs_revision', 'approved', 'rejected'], true))
                    <x-card>
                        <form method="POST" action="{{ route('room-requests.withdraw', $booking) }}" class="grid gap-3">
                            @csrf
                            <input type="hidden" name="version" value="{{ $booking->version }}">
                            @if ($booking->status === 'approved')
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="confirm_room_release" value="1" class="mt-1 accent-terracotta" required>
                                    Saya memahami reservasi lama akan dilepas dan ruang harus diajukan ulang.
                                </label>
                            @endif
                            <div><x-button variant="secondary">{{ $booking->status === 'approved' ? 'Lepas reservasi' : 'Tarik pengajuan' }}</x-button></div>
                        </form>
                    </x-card>
                @endif
            @endif

            @if ($booking->status === 'submitted')
                @can('review', $booking)
                    <x-card>
                        <h2 class="section-title">Keputusan peninjau</h2>
                        <form method="POST" action="{{ route('room-requests.decide', $booking) }}" class="mt-4 grid gap-4">
                            @csrf
                            <input type="hidden" name="version" value="{{ $booking->version }}">
                            <x-form-select id="booking-result" name="result" label="Keputusan" required>
                                <option value="">Pilih keputusan</option>
                                @foreach (['approved', 'needs_revision', 'rejected'] as $result)
                                    <option value="{{ $result }}" @selected(old('result') === $result)>{{ $booking::STATUSES[$result] }}</option>
                                @endforeach
                            </x-form-select>
                            <x-form-textarea id="booking-reason" name="reason" label="Alasan keputusan" rows="3" maxlength="3000" required>{{ old('reason') }}</x-form-textarea>
                            <div><x-button>Simpan keputusan</x-button></div>
                        </form>
                    </x-card>
                @endcan
            @endif
        </div>
    </div>
@endsection
