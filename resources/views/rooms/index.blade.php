@extends('layouts.workspace')

@section('title', 'Ruang')

@section('content')
    <header class="workspace-heading flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Cari ruang</h1>
            <p class="intro-text">Periksa kapasitas dan jadwal reservasi di RAPATIN.</p>
        </div>
        <a class="button button--secondary" href="{{ route('organizations.index') }}">Buka katalog organisasi</a>
        @if ($canReview)
            <a class="button button--secondary" href="{{ route('room-requests.review') }}">Antrean peninjauan</a>
        @endif
    </header>

    <x-card>
        <form method="GET" action="{{ route('rooms.index') }}" class="grid items-end gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @if ($organizations->isNotEmpty())
                <x-form-select id="room-organization" name="organization" label="Organisasi">
                    @foreach ($organizations as $option)
                        <option value="{{ $option->id }}" @selected($organization?->id === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </x-form-select>
            @endif
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
                    Pilih meeting offline yang belum dimulai, lalu klik Cari ruang.
                    @if ($meetings->isEmpty())
                        <a class="text-terracotta underline" href="{{ route('meetings.index') }}">Lihat atau ubah jadwal meeting.</a>
                    @endif
                </p>
            </div>
            <x-input id="room-people" name="people_count" type="number" min="1" label="Jumlah orang" :value="old('people_count', $people)" />
            <x-input id="room-start" name="start" type="datetime-local" label="Mulai jika cek waktu lain (WIB)" :value="request('start')" />
            <x-input id="room-end" name="end" type="datetime-local" label="Selesai jika cek waktu lain (WIB)" :value="request('end')" />
            <div class="sm:col-span-2 xl:col-span-4">
                <x-button>Cari ruang</x-button>
            </div>
        </form>
        @if ($organization)
            <p class="mt-4 text-sm text-muted">Katalog <strong class="text-ink">{{ $organization->name }}</strong></p>
        @endif
        @if ($start)
            <p class="mt-4 border-t border-line pt-3 text-sm text-muted">
                Jadwal yang diperiksa
                <strong class="text-ink">{{ $start->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}–{{ $end->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</strong>
            </p>
        @endif
    </x-card>

    <div class="mt-5 grid items-start gap-4 lg:grid-cols-2">
        @forelse ($rooms as $room)
            @php
                $shortage = max(0, $people - $room->capacity);
                $occupied = $reserved->contains($room->id);
            @endphp
            <x-card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="section-title wrap-anywhere">{{ $room->name }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ $buildings[$room->building_id] }} · {{ $room->capacity }} kursi</p>
                    </div>
                    @if ($start)
                        <x-status-badge :variant="$occupied ? 'warning' : 'success'">{{ $occupied ? 'Slot terisi' : 'Slot tersedia' }}</x-status-badge>
                    @endif
                </div>
                <div class="mt-4 grid gap-1 border-t border-line pt-3 text-sm">
                    @if ($shortage)
                        <p class="font-semibold text-terracotta">Kurang {{ $shortage }} kursi</p>
                    @endif
                    @if ($start)
                        <p class="text-muted">{{ $pending[$room->id] ?? 0 }} pengajuan menunggu</p>
                    @else
                        <p class="text-muted">Pilih jadwal untuk memeriksa slot.</p>
                    @endif
                </div>
                @if ($meeting && ! $occupied && ! $shortage && ! request('start'))
                    <a class="button button--secondary mt-4" href="{{ route('room-requests.form', ['meeting' => $meeting, 'room' => $room->id, 'organization' => $organization->id, 'people_count' => $people]) }}" aria-label="Ajukan {{ $room->name }}">Ajukan ruang</a>
                @endif
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
    </div>
@endsection
