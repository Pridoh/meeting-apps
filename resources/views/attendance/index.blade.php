@extends('layouts.workspace')

@section('title', 'Kehadiran')

@section('content')
    <header class="workspace-heading">
        <h1 class="page-title">Kehadiran</h1>
        <p class="intro-text">Tinjau jadwal undangan dan tentukan meeting yang akan Anda hadiri.</p>
    </header>

    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-lg border border-danger/35 bg-soft p-4 text-sm font-semibold text-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-4" aria-label="Pilihan jadwal">
        @forelse ($options as $option)
            @php
                $meeting = $option['meeting'];
                $start = $meeting->start_time->timezone($meeting->timezone);
                $end = $meeting->end_time->timezone($meeting->timezone);
                $deadline = $option['deadline']->timezone($meeting->timezone);
                [$label, $variant] = match ($option['state']) {
                    'will_attend' => ['Akan hadir', 'success'],
                    'not_attending' => ['Tidak akan hadir', 'neutral'],
                    'not_chosen' => ['Meeting lain dipilih', 'neutral'],
                    'reconfirm' => ['Konfirmasi ulang', 'warning'],
                    'unchosen' => ['Belum memilih sampai H-1', 'warning'],
                    'conflict' => ['Perlu pilihan', 'warning'],
                    default => ['Menunggu pilihan', 'neutral'],
                };
            @endphp
            <x-card aria-labelledby="attendance-{{ $meeting->id }}">
                <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 id="attendance-{{ $meeting->id }}" class="section-title wrap-anywhere">{{ $meeting->title }}</h2>
                            <x-status-badge :variant="$variant">{{ $label }}</x-status-badge>
                            @if ($option['late'])
                                <x-status-badge variant="warning">Koreksi terlambat</x-status-badge>
                            @endif
                        </div>
                        <p class="mt-2 text-sm font-semibold tabular-nums">
                            <time datetime="{{ $start->toIso8601String() }}">{{ $start->locale('id')->translatedFormat('d M Y, H.i') }}</time>
                            <span aria-hidden="true">–</span><span class="sr-only"> sampai </span>
                            <time datetime="{{ $end->toIso8601String() }}">{{ $end->locale('id')->translatedFormat($start->isSameDay($end) ? 'H.i' : 'd M Y, H.i') }}</time>
                            <span class="font-normal text-muted">{{ $meeting->timezone }}</span>
                        </p>
                        @if ($option['overlaps']->isNotEmpty())
                            <div class="mt-4 rounded-md border-s-2 border-clay bg-cream/60 px-3 py-2.5 text-sm">
                                <p class="font-semibold text-terracotta">Jadwal bertumpuk</p>
                                <ul class="mt-1 list-disc space-y-1 ps-5 text-muted">
                                    @foreach ($option['overlaps'] as $other)
                                        <li class="wrap-anywhere">{{ $other->title }} · {{ $other->start_time->timezone($meeting->timezone)->format('H.i') }}–{{ $other->end_time->timezone($meeting->timezone)->format('H.i') }}</li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-muted">Memilih hadir menggantikan pilihan hadir pada jadwal yang bertumpuk.</p>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-3 lg:min-w-52">
                        <p class="text-sm text-muted">
                            <span class="block font-semibold text-ink">Tenggat H-1</span>
                            <time datetime="{{ $deadline->toIso8601String() }}">{{ $deadline->locale('id')->translatedFormat('d M Y, H.i') }}</time>
                            {{ $meeting->timezone }}
                        </p>
                        @if ($meeting->start_time->gt($now))
                            <form method="POST" action="{{ route('attendance.store', $meeting) }}" class="flex flex-wrap gap-2" aria-label="Pilihan kehadiran {{ $meeting->title }}">
                                @csrf
                                <input type="hidden" name="schedule_version" value="{{ $meeting->schedule_version }}">
                                <x-button name="choice" value="will_attend">Pilih hadir</x-button>
                                <x-button variant="secondary" name="choice" value="not_attending">Tidak hadir</x-button>
                            </form>
                        @else
                            <p class="text-sm text-muted">Pilihan ditutup sejak meeting dimulai.</p>
                        @endif
                    </div>
                </div>
            </x-card>
        @empty
            <p class="empty-state">Belum ada undangan atau jadwal kehadiran yang aktif.</p>
        @endforelse
    </div>
@endsection
