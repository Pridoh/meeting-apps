@extends('layouts.workspace')

@section('title', 'Dashboard')

@section('content')
    <header class="workspace-heading flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <h1 class="page-title">Dashboard</h1>
            <p class="intro-text">Selamat datang, {{ auth()->user()->name }}.</p>
        </div>
        <a class="button button--primary" href="{{ route('meetings.create') }}"><span aria-hidden="true">+</span> Buat meeting</a>
    </header>

    <div class="grid gap-6">
        <section aria-labelledby="kelola-heading" class="grid gap-3">
            <h2 id="kelola-heading" class="section-title">Meeting yang Anda kelola</h2>
            <dl class="dashboard-metrics">
                @foreach (['total' => 'Total meeting', 'upcoming' => 'Mendatang', 'completed' => 'Selesai', 'pendingTasks' => 'Tugas tertunda'] as $key => $label)
                    <div class="dashboard-metric">
                        <dt class="text-sm font-semibold text-muted">{{ $label }}</dt>
                        <dd>{{ $organizerStats[$key] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <div class="dashboard-panels">
            <div class="grid min-w-0 gap-4 xl:gap-6">
                <x-card aria-labelledby="terbaru-heading">
                    <div class="flex flex-wrap items-center justify-between gap-x-4">
                        <h2 id="terbaru-heading" class="section-title">Meeting terbaru</h2>
                        <a class="back-link mb-0" href="{{ route('meetings.index') }}">Lihat meeting <span aria-hidden="true">→</span></a>
                    </div>
                    <ul class="mt-3 divide-y divide-line">
                        @forelse ($recentMeetings as $meeting)
                            <x-meeting-row :meeting="$meeting" />
                        @empty
                            <li class="empty-state">Anda belum membuat meeting.</li>
                        @endforelse
                    </ul>
                </x-card>

                <x-card aria-labelledby="jadwal-heading">
                    <h2 id="jadwal-heading" class="section-title">Jadwal yang Anda ikuti</h2>
                    <ul class="mt-3 divide-y divide-line">
                        @forelse ($upcomingMeetings as $meeting)
                            <x-meeting-row :meeting="$meeting" show-organizer />
                        @empty
                            <li class="empty-state">Belum ada jadwal mendatang.</li>
                        @endforelse
                    </ul>
                </x-card>
            </div>

            <x-card class="dashboard-tasks" aria-labelledby="tugas-heading">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="tugas-heading" class="section-title">Tugas pribadi</h2>
                    <a class="back-link mb-0" href="{{ route('action-items.index') }}">Lihat tugas <span aria-hidden="true">→</span></a>
                </div>
                @if ($participantTaskCount)
                    <p class="mt-3 text-sm font-semibold text-muted tabular-nums"><span class="text-lg text-terracotta">{{ $completedTaskCount }} dari {{ $participantTaskCount }}</span> tugas selesai</p>
                    <progress class="mt-2 block h-2 w-full accent-terracotta" max="{{ $participantTaskCount }}" value="{{ $completedTaskCount }}" aria-label="Progres tugas pribadi"></progress>
                    <p class="mt-3 text-sm text-muted">
                        @if ($nearestDeadline)
                            Tenggat terdekat <time datetime="{{ $nearestDeadline }}">{{ Illuminate\Support\Carbon::parse($nearestDeadline)->locale('id')->translatedFormat('d M Y') }}</time>
                        @else
                            Belum ada tenggat mendatang.
                        @endif
                    </p>

                    <ul class="mt-3 divide-y divide-line border-t border-line">
                        @foreach ($participantTasks as $task)
                            <li class="py-3">
                                <a class="block wrap-anywhere text-sm font-semibold text-terracotta underline-offset-4 hover:underline" href="{{ route('meetings.show', $task->meeting) }}#tugas-{{ $task->id }}">{{ $task->description }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="empty-state mt-3">Anda belum memiliki tugas.</p>
                @endif
            </x-card>
        </div>
    </div>
@endsection
