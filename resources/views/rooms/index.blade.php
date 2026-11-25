@extends('layouts.workspace')

@section('title', 'Ruang')

@section('content')
    <header class="workspace-heading flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Cari ruang</h1>
            <p class="intro-text">Pilih meeting untuk mengajukan ruang, atau periksa ketersediaan pada jadwal lain.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="button button--secondary" href="{{ route('organizations.index') }}">Organisasi saya</a>
            @if ($canReview)
                <a class="button button--secondary" href="{{ route('room-requests.review') }}">Antrean peninjauan</a>
            @endif
        </div>
    </header>

    <x-card>
        <form method="GET" action="{{ route('rooms.index') }}" class="grid gap-5">
            @if ($organizations->count() > 1)
                <x-form-select id="room-organization" name="organization" label="Organisasi">
                    @foreach ($organizations as $option)
                        <option value="{{ $option->id }}" @selected($organization?->id === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </x-form-select>
            @elseif ($organization)
                <div class="border-b border-line pb-4">
                    <p class="text-sm text-muted">Organisasi</p>
                    <p class="mt-1 wrap-anywhere font-semibold">{{ $organization->name }}</p>
                    <input type="hidden" name="organization" value="{{ $organization->id }}">
                </div>
            @endif
            <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_12rem]">
                <div class="min-w-0">
                    <x-form-select id="room-meeting" name="meeting" label="Meeting (opsional)" aria-describedby="room-meeting-help">
                        <option value="">Pilih meeting</option>
                        @foreach ($meetings as $option)
                            <option value="{{ $option->id }}" @selected($meeting?->id === $option->id)>{{ $option->title }}</option>
                        @endforeach
                        @foreach ($unavailableMeetings as $option)
                            @php
                                $reason = match (true) {
                                    $option->mode !== 'offline' => 'meeting online',
                                    $option->status === 'completed' => 'meeting selesai',
                                    default => 'meeting sudah dimulai',
                                };
                            @endphp
                            <option disabled>{{ $option->title }} · {{ $reason }}</option>
                        @endforeach
                    </x-form-select>
                    <p id="room-meeting-help" class="mt-2 text-sm text-muted">
                        Hanya meeting tatap muka yang belum dimulai yang dapat diajukan.
                        @if ($meetings->isEmpty())
                            <a class="text-terracotta underline" href="{{ route('meetings.index') }}">Lihat atau ubah jadwal meeting.</a>
                        @endif
                    </p>
                </div>
                <x-input id="room-people" name="people_count" type="number" min="1" label="Jumlah orang" :value="old('people_count', $people)" />
            </div>
            <details class="border-y border-line py-3" @if (request()->filled('start') || request()->filled('end') || $errors->hasAny(['start', 'end'])) open @endif>
                <summary class="cursor-pointer py-1 text-sm font-semibold text-terracotta">Periksa jadwal lain</summary>
                <p class="mt-2 text-sm text-muted">Isi kedua waktu untuk melihat ketersediaan tanpa mengajukan reservasi.</p>
                <div class="mt-3 grid gap-4 lg:grid-cols-2">
                    <x-input id="room-start" name="start" type="datetime-local" label="Mulai (WIB)" :value="old('start', request('start'))" />
                    <x-input id="room-end" name="end" type="datetime-local" label="Selesai (WIB)" :value="old('end', request('end'))" />
                </div>
            </details>
            <div class="flex flex-wrap items-center gap-4">
                <x-button>Cari ruang</x-button>
                @if (request()->filled('start'))
                    <a class="back-link mb-0" href="{{ route('rooms.index', request()->only('meeting', 'organization', 'people_count')) }}">Hapus waktu khusus</a>
                @endif
            </div>
        </form>
        @if ($start)
            <p class="mt-4 border-t border-line pt-3 text-sm text-muted">
                Jadwal yang diperiksa
                <strong class="text-ink">{{ $start->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}–{{ $end->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</strong>
            </p>
        @endif
    </x-card>

    <section class="mt-8 grid gap-5" aria-label="Hasil pencarian ruang">
        @forelse ($rooms->groupBy('building_id') as $buildingId => $buildingRooms)
            <x-card>
                <h2 class="section-title">{{ $buildings[$buildingId] }}</h2>
                <ul class="mt-3 divide-y divide-line border-t border-line">
                    @foreach ($buildingRooms as $room)
                        @php
                            $shortage = max(0, $people - $room->capacity);
                            $occupied = $reserved->contains($room->id);
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <h3 class="wrap-anywhere font-semibold">{{ $room->name }}</h3>
                                <p class="text-sm text-muted">{{ $room->capacity }} kursi</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($shortage)
                                    <span class="text-sm font-semibold text-terracotta">Kurang {{ $shortage }} kursi</span>
                                @endif
                                @if ($start)
                                    <x-status-badge :variant="$occupied ? 'warning' : 'success'">{{ $occupied ? 'Slot terisi' : 'Slot tersedia' }}</x-status-badge>
                                    @if ($pending[$room->id] ?? 0)
                                        <span class="text-sm text-muted">{{ $pending[$room->id] }} pengajuan menunggu</span>
                                    @endif
                                @endif
                                @if ($meeting && ! $occupied && ! $shortage && ! request('start'))
                                    <a class="button button--secondary" href="{{ route('room-requests.form', ['meeting' => $meeting, 'room' => $room->id, 'organization' => $organization->id, 'people_count' => $people]) }}" aria-label="Ajukan {{ $room->name }}">Ajukan ruang</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @empty
            <p class="empty-state">
                @if ($organization)
                    Organisasi ini belum memiliki ruang aktif.
                @else
                    <a class="text-terracotta underline" href="{{ route('organizations.index') }}">Buat atau gabung organisasi</a> untuk melihat ruang.
                @endif
            </p>
        @endforelse
    </section>
@endsection
