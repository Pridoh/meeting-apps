@extends('layouts.workspace')

@section('title', 'Dashboard')

@section('content')
    <header class="workspace-heading">
        <h1 class="page-title">Dashboard</h1>
        <p class="intro-text">Selamat datang, {{ auth()->user()->name }}.</p>
    </header>

    <x-card aria-labelledby="akun-aktif" class="account-card">
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
            <h2 id="akun-aktif" class="text-xl font-semibold text-terracotta">Akun Anda</h2>
            <x-status-badge variant="success">Sesi aktif</x-status-badge>
        </div>
        <dl class="mt-5 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-muted">Nama</dt>
                <dd class="mt-1 wrap-anywhere font-medium">{{ auth()->user()->name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-muted">Email</dt>
                <dd class="mt-1 wrap-anywhere font-medium">{{ auth()->user()->email }}</dd>
            </div>
        </dl>
    </x-card>
@endsection
