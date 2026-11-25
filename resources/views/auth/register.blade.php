@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <h1 class="page-title">Daftar akun</h1>
    <p class="intro-text">Buat akun untuk mengatur meeting atau merespons undangan.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-4">
        @csrf
        <x-input id="name" name="name" label="Nama" :value="old('name')" autocomplete="name" required />
        <x-input id="email" name="email" label="Alamat email" type="email" :value="old('email')" autocomplete="email" required />
        @if (session()->has('invite_hash'))
            <p class="-mt-2 text-sm text-muted">Untuk merespons undangan ini, gunakan alamat email yang menerima undangan.</p>
        @endif
        <x-input id="password" name="password" label="Kata sandi (minimal 8 karakter)" type="password" autocomplete="new-password" required />
        <x-input id="password_confirmation" name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required />
        <x-button class="mt-1">Daftar</x-button>
    </form>
    <a class="back-link mt-3 mb-0" href="{{ route('login') }}">Sudah punya akun? Masuk</a>
@endsection
