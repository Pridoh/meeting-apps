@extends('layouts.app')

@section('body')
    <div class="workspace">
        <aside class="workspace-sidebar">
            <x-brand :href="route('dashboard')" />
            <nav aria-label="Navigasi utama">
                <a class="workspace-nav-link" href="{{ route('dashboard') }}" aria-current="page">
                    <span class="dashboard-icon" aria-hidden="true"></span>
                    Dashboard
                </a>
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
            @yield('content')
        </main>

        <x-confirmation-modal id="konfirmasi-keluar" title="Keluar dari RAPATIN?" confirm-label="Keluar">
            Anda perlu masuk kembali untuk membuka dashboard.
        </x-confirmation-modal>
    </div>
@endsection
