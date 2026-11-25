@extends('layouts.app')

@section('body')
    <div class="workspace">
        <aside class="workspace-sidebar">
            <x-brand :href="route('dashboard')" />
            <nav aria-label="Navigasi utama">
                <a class="workspace-nav-link" href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                <a class="workspace-nav-link" href="{{ route('meetings.index') }}" @if (request()->routeIs('meetings.*', 'notes.*')) aria-current="page" @endif>Meeting</a>
                <a class="workspace-nav-link" href="{{ route('action-items.index') }}" @if (request()->routeIs('action-items.*')) aria-current="page" @endif>Tugas</a>
                <a class="workspace-nav-link" href="{{ route('attendance.index') }}" @if (request()->routeIs('attendance.*')) aria-current="page" @endif>Kehadiran</a>
                <a class="workspace-nav-link" href="{{ route('rooms.index') }}" @if (request()->routeIs('rooms.*', 'room-requests.*')) aria-current="page" @endif>Ruang</a>
                <a class="workspace-nav-link" href="{{ route('organizations.index') }}" @if (request()->routeIs('organizations.*')) aria-current="page" @endif>Organisasi</a>
            </nav>
            <div class="workspace-account">
                <p class="workspace-account-name">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}" data-confirm-with="konfirmasi-keluar">
                    @csrf
                    <x-button variant="sidebar" aria-haspopup="dialog">Keluar</x-button>
                </form>
            </div>
        </aside>

        <main id="konten-utama" tabindex="-1" class="workspace-content">
            @if (session('success'))
                <p role="status" class="success-notice mb-5">{{ session('success') }}</p>
            @endif
            @yield('content')
        </main>

        <x-confirmation-modal id="konfirmasi-keluar" title="Keluar dari RAPATIN?" confirm-label="Keluar">
            Anda perlu masuk kembali untuk membuka dashboard.
        </x-confirmation-modal>
    </div>
@endsection
