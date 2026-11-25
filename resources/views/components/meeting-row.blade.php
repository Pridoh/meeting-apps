@props(['meeting', 'showOrganizer' => false])

@php
    $start = $meeting->start_time->timezone($meeting->timezone);
@endphp

<li class="flex flex-wrap items-start justify-between gap-x-5 gap-y-2 py-4">
    <div class="min-w-0">
        <p class="text-sm text-muted">
            <time datetime="{{ $start->toIso8601String() }}">{{ $start->locale('id')->translatedFormat('d M Y · H.i T') }}</time>
            · {{ ucfirst($meeting->mode) }}
        </p>
        <a class="mt-1 block wrap-anywhere font-semibold text-terracotta underline-offset-4 hover:underline" href="{{ route('meetings.show', $meeting) }}">{{ $meeting->title }}</a>
        @if ($showOrganizer)
            <p class="mt-1 wrap-anywhere text-sm text-muted">
                {{ $meeting->user_id === auth()->id() ? 'Anda · Organizer' : $meeting->organizer->name.' · Organizer' }}
            </p>
        @endif
    </div>
    <x-meeting-status :status="$meeting->status" />
</li>
