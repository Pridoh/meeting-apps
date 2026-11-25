@extends('layouts.app')

@section('body')
    <div class="workspace">
        <aside class="workspace-sidebar">
            <x-brand :href="route('dashboard')" />
            <nav aria-label="Navigasi utama">
                <a class="workspace-nav-link" href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                <a class="workspace-nav-link" href="{{ route('meetings.index') }}" @if (request()->routeIs('meetings.*', 'notes.*')) aria-current="page" @endif>Meeting</a>
                <a class="workspace-nav-link" href="{{ route('action-items.index') }}" @if (request()->routeIs('action-items.*')) aria-current="page" @endif>Tugas</a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" data-confirm-with="konfirmasi-keluar">
                @csrf
                <x-button variant="sidebar" aria-haspopup="dialog">
                    <span aria-hidden="true">↗</span>
                    Keluar
                </x-button>
            </form>
        </aside>

        <main id="konten-utama" tabindex="-1" class="workspace-content">
            @if (session('success'))
                <p role="status" class="mb-5 rounded-md border border-success/40 bg-soft px-4 py-3 text-sm font-semibold">{{ session('success') }}</p>
            @endif
            @yield('content')
        </main>

        <x-confirmation-modal id="konfirmasi-keluar" title="Keluar dari RAPATIN?" confirm-label="Keluar">
            Anda perlu masuk kembali untuk membuka dashboard.
        </x-confirmation-modal>
    </div>
@endsection
