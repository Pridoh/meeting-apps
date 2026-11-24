@extends('layouts.app')

@section('title', 'Masuk')

@section('body')
    <div class="login-layout">
        <header class="login-brand-panel">
            <x-brand :href="route('home')" />

            <div class="table-plan" aria-hidden="true">
                <span class="table-plan-center"></span>
            </div>
        </header>

        <main id="konten-utama" tabindex="-1" class="login-main">
            <div class="login-content">
                <a class="back-link" href="{{ route('home') }}">
                    <span aria-hidden="true">←</span> Beranda
                </a>

                <h1 class="page-title">Masuk ke RAPATIN</h1>
                <p class="intro-text">Gunakan email dan kata sandi akun Anda.</p>

                @php($email = old('email'))

                <form class="mt-8 grid gap-5" method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf
                    <x-input id="email" name="email" label="Email" type="email" :value="is_string($email) ? $email : null"
                        autocomplete="username" required />
                    <x-input id="password" name="password" label="Kata sandi" type="password"
                        autocomplete="current-password" required />
                    <x-button class="mt-1 w-full">
                        Masuk <span aria-hidden="true">→</span>
                    </x-button>
                </form>
            </div>
        </main>
    </div>
@endsection
