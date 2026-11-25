@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <a class="back-link" href="{{ route('home') }}"><span aria-hidden="true">←</span> Beranda</a>
    <h1 class="page-title">Masuk ke RAPATIN</h1>
    <p class="intro-text">Gunakan email dan kata sandi akun Anda.</p>

    <form class="mt-7 grid gap-5" method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf
        <x-input id="email" name="email" label="Email" type="email" :value="old('email')" autocomplete="username" required />
        <x-input id="password" name="password" label="Kata sandi" type="password" autocomplete="current-password" required />
        <x-button class="mt-1 w-full">Masuk <span aria-hidden="true">→</span></x-button>
    </form>
    <p class="mt-5 text-sm text-muted">
        Belum punya akun?
        <a class="font-semibold text-terracotta underline underline-offset-4" href="{{ route('register') }}">Daftar akun</a>
    </p>
@endsection
