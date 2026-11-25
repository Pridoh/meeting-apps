@extends('layouts.workspace')

@section('title', 'Dashboard')

@section('content')
    <header class="workspace-heading flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="intro-text">Selamat datang, {{ auth()->user()->name }}.</p>
        </div>
        <a class="button button--primary" href="{{ route('meetings.create') }}">Buat meeting</a>
    </header>

    <div class="grid gap-7">
        <section aria-labelledby="kelola-heading" class="grid gap-4">
            <h2 id="kelola-heading" class="section-title">Meeting yang Anda kelola</h2>
            <dl class="grid grid-cols-2 overflow-hidden rounded-xl border border-line bg-line gap-px sm:grid-cols-4">
                @foreach (['total' => 'Total meeting', 'upcoming' => 'Mendatang', 'completed' => 'Selesai', 'pendingTasks' => 'Tugas tertunda'] as $key => $label)
                    <div class="grid content-start gap-1 bg-soft p-4">
                        <dt class="text-sm font-semibold text-muted">{{ $label }}</dt>
                        <dd class="text-3xl font-semibold tabular-nums text-terracotta">{{ $organizerStats[$key] }}</dd>
                    </div>
                @endforeach
            </dl>
            <x-card aria-labelledby="terbaru-heading">
                <h3 id="terbaru-heading" class="section-title">Meeting terbaru</h3>
                <ul class="mt-2 divide-y divide-line">
                    @forelse ($recentMeetings as $meeting)
                        <x-meeting-row :meeting="$meeting" />
                    @empty
                        <li class="mt-3 text-muted">Anda belum membuat meeting.</li>
                    @endforelse
                </ul>
            </x-card>
        </section>

        <section aria-labelledby="ikuti-heading" class="grid gap-4">
            <h2 id="ikuti-heading" class="section-title">Meeting yang Anda ikuti</h2>
            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                <x-card aria-labelledby="jadwal-heading">
                    <h3 id="jadwal-heading" class="section-title">Jadwal mendatang</h3>
                    <ul class="mt-2 divide-y divide-line">
                        @forelse ($upcomingMeetings as $meeting)
                            <x-meeting-row :meeting="$meeting" show-organizer />
                        @empty
                            <li class="mt-3 text-muted">Belum ada jadwal mendatang.</li>
                        @endforelse
                    </ul>
                </x-card>

                <x-card aria-labelledby="tugas-heading">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 id="tugas-heading" class="section-title">Tugas pribadi</h3>
                        <a class="inline-flex min-h-11 items-center text-sm font-semibold text-terracotta underline-offset-4 hover:underline" href="{{ route('action-items.index') }}">Lihat tugas</a>
                    </div>
                    <p class="mt-2 text-sm font-semibold">{{ $completedTaskCount }} dari {{ $participantTaskCount }} tugas selesai</p>
                    @if ($participantTaskCount)
                        <progress class="mt-2 block h-2 w-full accent-terracotta" max="{{ $participantTaskCount }}" value="{{ $completedTaskCount }}" aria-label="Progres tugas pribadi"></progress>
                    @endif
                    <p class="mt-3 text-sm text-muted">
                        @if ($nearestDeadline)
                            Tenggat terdekat <time datetime="{{ $nearestDeadline }}">{{ Illuminate\Support\Carbon::parse($nearestDeadline)->locale('id')->translatedFormat('d M Y') }}</time>
                        @else
                            Belum ada tenggat mendatang.
                        @endif
                    </p>
                    @if ($participantTasks->isNotEmpty())
                        <ul class="mt-3 divide-y divide-line border-t border-line">
                            @foreach ($participantTasks as $task)
                                <li class="py-3">
                                    <a class="block wrap-anywhere text-sm font-semibold text-terracotta underline-offset-4 hover:underline" href="{{ route('meetings.show', $task->meeting) }}#tugas-{{ $task->id }}">{{ $task->description }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>
        </section>
    </div>
@endsection
