@props(['meeting', 'showOrganizer' => false])

@php
    $start = $meeting->start_time->timezone($meeting->timezone);
@endphp

<li class="meeting-row">
    <span class="meeting-date" aria-hidden="true">
        <span class="meeting-date-day">{{ $start->format('d') }}</span>
        <span>{{ $start->locale('id')->translatedFormat('M') }}</span>
        <span class="meeting-date-year">{{ $start->format('Y') }}</span>
    </span>
    <div class="min-w-0">
        <p class="text-sm text-muted tabular-nums">
            <time datetime="{{ $start->toIso8601String() }}"><span class="sr-only">{{ $start->locale('id')->translatedFormat('d M Y, ') }}</span>{{ $start->format('H.i T') }}</time>
            <span aria-hidden="true">·</span> {{ ucfirst($meeting->mode) }}
        </p>
        <a class="meeting-row-title" href="{{ route('meetings.show', $meeting) }}">{{ $meeting->title }}</a>
        @if ($showOrganizer)
            <p class="mt-1 wrap-anywhere text-sm text-muted">
                {{ $meeting->user_id === auth()->id() ? 'Anda · Organizer' : $meeting->organizer->name.' · Organizer' }}
            </p>
        @endif
    </div>
    <div class="meeting-row-status"><x-meeting-status :status="$meeting->status" /></div>
</li>
