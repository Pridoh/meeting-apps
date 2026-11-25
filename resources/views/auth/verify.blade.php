@extends('layouts.guest')

@section('title', 'Verifikasi email')

@section('content')
    <h1 class="page-title">Verifikasi email</h1>
    <p class="intro-text">Buka tautan verifikasi yang dikirim ke <strong>{{ auth()->user()->email }}</strong> untuk melanjutkan.</p>

    @if (session('success'))
        <p role="status" class="success-notice mt-5">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <x-button variant="secondary">Kirim email verifikasi</x-button>
    </form>
@endsection
