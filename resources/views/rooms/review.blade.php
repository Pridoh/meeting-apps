@extends('layouts.workspace')

@section('title', 'Antrean peninjauan')

@section('content')
    <header class="workspace-heading">
        <a class="back-link" href="{{ route('rooms.index', ['organization' => $organization->id]) }}">Kembali ke katalog ruang</a>
        <h1 class="page-title">Antrean peninjauan</h1>
        <p class="intro-text">Pengajuan untuk ruang yang Anda tinjau di {{ $organization->name }}.</p>
    </header>

    @if ($organizations->count() > 1)
        <x-card class="mb-4">
            <form method="GET" action="{{ route('room-requests.review') }}" class="flex flex-wrap items-end gap-3">
                <x-form-select id="review-organization" name="organization" label="Organisasi">
                    @foreach ($organizations as $option)
                        <option value="{{ $option->id }}" @selected($organization->id === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </x-form-select>
                <x-button variant="secondary">Lihat antrean</x-button>
            </form>
        </x-card>
    @endif

    <div class="grid gap-3">
        @forelse ($bookings as $booking)
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="section-title wrap-anywhere">{{ $booking->meeting->title }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ $buildings[$booking->room->building_id] }} · {{ $booking->room->name }} · {{ $booking->requester->name }} · {{ $booking->people_count }} orang</p>
                        <p class="mt-1 text-sm text-muted">{{ $booking->start_time->timezone($booking->meeting->timezone)->format('d/m/Y H:i') }} ({{ $booking->meeting->timezone }})</p>
                    </div>
                    <a class="button button--secondary" href="{{ route('room-requests.show', $booking) }}" aria-label="Tinjau {{ $booking->meeting->title }}">Tinjau pengajuan</a>
                </div>
            </x-card>
        @empty
            <p class="empty-state">Tidak ada pengajuan yang menunggu keputusan.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
@endsection
