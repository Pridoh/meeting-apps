@extends('layouts.workspace')

@section('title', $organization->name)

@section('content')
    <header class="workspace-heading">
        <a class="back-link" href="{{ route('organizations.index') }}">Kembali ke organisasi</a>
        <h1 class="page-title wrap-anywhere">{{ $organization->name }}</h1>
        <p class="intro-text">{{ $organization::CATEGORIES[$organization->category] }}</p>
        <a class="button button--primary mt-4" href="{{ route('rooms.index', ['organization' => $organization->id]) }}">Cari ruang organisasi</a>
    </header>

    @if ($errors->any())
        <ul role="alert" class="mb-5 grid gap-1 rounded-lg border border-danger/35 p-4 text-sm text-danger">
            @foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
    @endif

    @if ($owner)
        <x-card class="mb-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <h2 class="section-title">Kode bergabung</h2>
                    <code class="mt-2 inline-block rounded bg-cream p-2 text-sm font-semibold text-terracotta">{{ $organization->join_code }}</code>
                </div>
                <div>
                    <h2 class="section-title">Anggota</h2>
                    <ul class="mt-2 grid gap-1 text-sm text-muted">
                        @foreach ($members as $member)
                            <li class="wrap-anywhere">{{ $member->name }}{{ $member->id === $organization->owner_id ? ' · Pemilik' : '' }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </x-card>
    @endif

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div class="grid min-w-0 gap-4">
            <h2 class="section-title">Katalog ruang</h2>
            @forelse ($buildings as $building)
                @continue(! $owner && ! $building->is_active)
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="section-title wrap-anywhere">{{ $building->name }}</h3>
                        @unless ($building->is_active)<x-status-badge>Nonaktif</x-status-badge>@endunless
                    </div>
                    @if ($building->location)<p class="text-sm text-muted">{{ $building->location }}</p>@endif
                    <ul class="mt-3 divide-y divide-line">
                        @foreach ($rooms->get($building->id, collect()) as $room)
                            @continue(! $owner && ! $room->is_active)
                            <li class="py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="wrap-anywhere font-semibold">{{ $room->name }}
                                        <span class="block text-sm font-normal text-muted">{{ $room->capacity }} kursi{{ $room->is_active ? '' : ' · Nonaktif' }}</span>
                                    </p>
                                    @if ($managedRooms->contains($room->id))
                                        <x-status-badge variant="success">Manajer ruang</x-status-badge>
                                    @elseif ($room->is_active && $building->is_active)
                                        <form method="POST" action="{{ route('organizations.manager.apply', [$organization, $room]) }}">
                                            @csrf
                                            <x-button variant="secondary" :aria-label="'Ajukan jadi manajer ruang '.$room->name">Ajukan jadi manajer</x-button>
                                        </form>
                                    @endif
                                </div>
                                @if ($owner)
                                    <details class="mt-3 text-sm">
                                        <summary class="cursor-pointer font-semibold text-terracotta">Ubah ruang</summary>
                                        @include('rooms.organization-room-form', ['editing' => $room])
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($owner)
                        <details class="mt-3 border-t border-line pt-3 text-sm">
                            <summary class="cursor-pointer font-semibold text-terracotta">Ubah gedung</summary>
                            @include('rooms.organization-building-form', ['editing' => $building])
                        </details>
                    @endif
                </x-card>
            @empty
                <p class="empty-state">Katalog ruang belum tersedia.</p>
            @endforelse
        </div>

        <div class="grid min-w-0 gap-4">
            @if ($owner)
                <x-card>
                    <h2 class="section-title">Tambah gedung</h2>
                    @include('rooms.organization-building-form', ['editing' => null])
                </x-card>
                @if ($buildings->isNotEmpty())
                    <x-card>
                        <h2 class="section-title">Tambah ruang</h2>
                        @include('rooms.organization-room-form', ['editing' => null])
                    </x-card>
                @endif
            @endif

            <x-card>
                <h2 class="section-title">{{ $owner ? 'Pengajuan manajer' : 'Pengajuan manajer saya' }}</h2>
                <ul class="mt-3 divide-y divide-line">
                    @forelse ($applications as $application)
                        <li class="grid gap-2 py-3 text-sm">
                            <p class="wrap-anywhere font-semibold">{{ $application->room_name }}{{ $owner ? ' · '.$application->applicant_name : '' }}</p>
                            <div><x-status-badge>{{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$application->status] }}</x-status-badge></div>
                            @if ($owner && $application->status === 'pending')
                                <form method="POST" action="{{ route('organizations.manager.decide', [$organization, $application->id]) }}" class="flex flex-wrap gap-2">
                                    @csrf
                                    <x-button name="status" value="approved">Setujui</x-button>
                                    <x-button name="status" value="rejected" variant="secondary">Tolak</x-button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-muted">Belum ada pengajuan.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>
@endsection
