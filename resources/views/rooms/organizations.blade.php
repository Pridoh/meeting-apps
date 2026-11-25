@extends('layouts.workspace')

@section('title', 'Organisasi')

@section('content')
    <header class="workspace-heading">
        <h1 class="page-title">Organisasi</h1>
        <p class="intro-text">Buka katalog ruang organisasi Anda atau gabung dengan kode.</p>
    </header>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <div class="grid gap-4">
            @forelse ($organizations as $organization)
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="section-title wrap-anywhere">{{ $organization->name }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ $organization::CATEGORIES[$organization->category] }} · {{ $organization->owner_id === auth()->id() ? 'Pemilik' : 'Anggota' }}</p>
                        </div>
                        <a class="button button--secondary" href="{{ route('organizations.show', $organization) }}" aria-label="Buka {{ $organization->name }}">Buka organisasi</a>
                    </div>
                </x-card>
            @empty
                <p class="empty-state">Anda belum bergabung dengan organisasi.</p>
            @endforelse
        </div>

        <div class="grid min-w-0 gap-4">
            <x-card>
                <h2 class="section-title">Gabung organisasi</h2>
                <form method="POST" action="{{ route('organizations.join') }}" class="mt-4 grid gap-3">
                    @csrf
                    <x-input id="join-code" name="join_code" label="Kode bergabung" maxlength="24" autocomplete="off" required />
                    <div><x-button>Gabung</x-button></div>
                </form>
            </x-card>
            <x-card>
                <h2 class="section-title">Buat organisasi</h2>
                <form method="POST" action="{{ route('organizations.store') }}" class="mt-4 grid gap-3">
                    @csrf
                    <x-input id="organization-name" name="name" label="Nama organisasi" :value="old('name')" maxlength="120" required />
                    <x-form-select id="organization-category" name="category" label="Kategori" :error="$errors->first('category')" required>
                        @foreach (\App\Modules\Rooms\Models\Organization::CATEGORIES as $category => $label)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $label }}</option>
                        @endforeach
                    </x-form-select>
                    <div><x-button variant="secondary">Buat organisasi</x-button></div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
