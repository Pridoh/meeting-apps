@extends('layouts.workspace')

@section('title', $meeting->title)

@section('content')
    @php
        $start = $meeting->start_time->timezone($meeting->timezone);
        $end = $meeting->end_time->timezone($meeting->timezone);
    @endphp

    <header class="workspace-heading">
        <a class="back-link" href="{{ route('meetings.index') }}">← Meeting</a>
        <div class="grid justify-items-start gap-3">
            <x-meeting-status :status="$meeting->status" />
            <h1 class="page-title wrap-anywhere">{{ $meeting->title }}</h1>
        </div>
        <p class="mt-3 wrap-anywhere text-muted tabular-nums">
            <time datetime="{{ $start->toIso8601String() }}">{{ $start->locale('id')->translatedFormat('d M Y, H.i') }}</time>
            <span aria-hidden="true">–</span><span class="sr-only"> sampai </span>
            <time datetime="{{ $end->toIso8601String() }}">{{ $end->locale('id')->translatedFormat($start->isSameDay($end) ? 'H.i' : 'd M Y, H.i') }}</time>
            <span>({{ $meeting->timezone }})</span>
        </p>

        @if ($canOpenNotes || $isOrganizer)
            <div class="mt-4 flex flex-wrap gap-3">
                @if ($canOpenNotes)
                    <a class="button button--primary" href="{{ route('notes.show', $meeting) }}">Buka notulen</a>
                @endif
                @if ($isOrganizer)
                    <a class="button button--secondary" href="{{ route('meetings.edit', $meeting) }}">Ubah meeting</a>
                    @if ($meeting->status !== 'completed')
                        <form method="POST" action="{{ route('meetings.status', $meeting) }}" @if ($meeting->status === 'ongoing') data-confirm-with="konfirmasi-selesai" @endif>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $meeting->status === 'scheduled' ? 'ongoing' : 'completed' }}">
                            <x-button variant="secondary" :aria-haspopup="$meeting->status === 'ongoing' ? 'dialog' : null">
                                {{ $meeting->status === 'scheduled' ? 'Mulai meeting' : 'Selesaikan meeting' }}
                            </x-button>
                        </form>
                    @endif
                @endif
            </div>
        @endif
        @if (! old('_tugas'))
            @error('status')
                <p role="alert" class="form-error">{{ $message }}</p>
            @enderror
        @endif
    </header>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div class="grid min-w-0 gap-5">
            <x-card aria-labelledby="detail-heading">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="detail-heading" class="section-title">Detail</h2>
                    @if ($isOrganizer && $meeting->mode === 'offline')
                        <a class="button button--secondary" href="{{ route('attendance.summary', $meeting) }}">Rekap kehadiran</a>
                    @endif
                </div>
                <dl class="mt-4 grid gap-x-5 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="field-label mb-1">Organizer</dt>
                        <dd class="wrap-anywhere text-muted">{{ $meeting->organizer->name }}</dd>
                    </div>
                    <div>
                        <dt class="field-label mb-1">Mode</dt>
                        <dd class="text-muted">{{ ucfirst($meeting->mode) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="field-label mb-1">Lokasi atau tautan</dt>
                        <dd class="wrap-anywhere text-muted">
                            @if (preg_match('~^https?://~i', $meeting->location ?? '') && filter_var($meeting->location, FILTER_VALIDATE_URL))
                                <a class="text-terracotta underline underline-offset-4" href="{{ $meeting->location }}">{{ $meeting->location }}</a>
                            @else
                                {{ $meeting->location ?? 'Belum diisi' }}
                            @endif
                        </dd>
                    </div>
                    @if ($meeting->description)
                        <div class="sm:col-span-2">
                            <dt class="field-label mb-1">Deskripsi</dt>
                            <dd class="wrap-anywhere whitespace-pre-line text-muted">{{ $meeting->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>
            @include('meetings.agendas')
        </div>

        <div class="grid min-w-0 gap-5">
            @if ($isOrganizer)
                @include('meetings.participants')
            @endif
            <x-card aria-labelledby="tugas-heading">
                <h2 id="tugas-heading" class="section-title">{{ $isOrganizer ? 'Tugas meeting' : 'Tugas Anda' }}</h2>
                <ul class="mt-4 divide-y divide-line">
                    @forelse ($actionItems as $item)
                        @include('action-items.row', ['participants' => $isOrganizer ? $meeting->participants : null])
                    @empty
                        <li class="empty-state">Belum ada tugas.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>

    @if ($isOrganizer)
        <form method="POST" action="{{ route('meetings.destroy', $meeting) }}" data-confirm-with="konfirmasi-hapus-meeting" class="mt-6">
            @csrf
            @method('DELETE')
            <x-button variant="secondary" class="text-danger" aria-haspopup="dialog">Hapus meeting</x-button>
        </form>

        <x-confirmation-modal id="konfirmasi-hapus-meeting" title="Hapus meeting?" confirm-label="Hapus meeting">
            Peserta akan kehilangan akses ke meeting ini dan menerima pemberitahuan.
        </x-confirmation-modal>
        @if ($meeting->status === 'ongoing')
            <x-confirmation-modal id="konfirmasi-selesai" title="Selesaikan meeting?" confirm-label="Selesaikan meeting">
                Status selesai bersifat final.
            </x-confirmation-modal>
        @endif
    @endif
@endsection
