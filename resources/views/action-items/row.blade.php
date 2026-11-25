@php
    $statuses = [
        'pending' => ['Tertunda', 'warning'],
        'in_progress' => ['Dikerjakan', 'info'],
        'completed' => ['Selesai', 'success'],
    ];
    [$statusLabel, $statusVariant] = $statuses[$item->status];
    $id = 'tugas-'.$item->id;
    $submitted = old('_tugas') === (string) $item->id;
    $assigneeId = $submitted ? old('assignee_user_id', $item->assignee_user_id) : $item->assignee_user_id;
@endphp

<li class="grid gap-3 py-5 first:pt-0 last:pb-0" id="{{ $id }}">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <x-status-badge :variant="$statusVariant">{{ $statusLabel }}</x-status-badge>
        <p class="text-sm text-muted tabular-nums">
            @if ($item->deadline)
                Tenggat <time datetime="{{ $item->deadline->toDateString() }}">{{ $item->deadline->locale('id')->translatedFormat('d M Y') }}</time>
            @else
                Tanpa tenggat
            @endif
        </p>
    </div>
    @if ($showMeeting ?? false)
        <h2 class="wrap-anywhere text-lg font-semibold">{{ $item->description }}</h2>
        <p><a class="wrap-anywhere text-sm font-semibold text-terracotta underline-offset-4 hover:underline" href="{{ route('meetings.show', $meeting) }}">{{ $meeting->title }}</a></p>
    @else
        <h3 class="wrap-anywhere font-semibold">{{ $item->description }}</h3>
    @endif
    <p class="wrap-anywhere text-sm text-muted">{{ $item->assignee->name ?? 'Belum ditugaskan' }}</p>

    <form method="POST" action="{{ route('meetings.action-items.update', [$meeting, $item]) }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-line bg-cream/35 p-3">
        @csrf
        @method('PATCH')
        <input type="hidden" name="_tugas" value="{{ $item->id }}">
        <x-form-select :id="$id.'-status'" name="status" label="Status tugas" :error="$submitted ? $errors->first('status') : null" required>
            @foreach ($statuses as $status => [$label])
                <option value="{{ $status }}" @selected(($submitted ? old('status', $item->status) : $item->status) === $status)>{{ $label }}</option>
            @endforeach
        </x-form-select>
        @if (isset($participants))
            <x-form-select :id="$id.'-peserta'" name="assignee_user_id" label="Penanggung jawab" :error="$submitted ? $errors->first('assignee_user_id') : null">
                @if ($assigneeId && ! $participants->contains('id', $assigneeId))
                    <option selected disabled>Pilih peserta</option>
                @endif
                <option value="" @selected(! $assigneeId)>Belum ditugaskan</option>
                @foreach ($participants as $participant)
                    <option value="{{ $participant->id }}" @selected($participant->id == $assigneeId)>{{ $participant->name }}</option>
                @endforeach
            </x-form-select>
        @endif
        <x-button variant="secondary" class="min-h-12" :aria-label="'Simpan tugas: '.$item->description">Simpan tugas</x-button>
    </form>
</li>
