@extends('layouts.guest')

@section('title', 'Akses terbatas')

@section('content')
    <h1 class="page-title">Akses terbatas</h1>
    <p class="intro-text">Akun Anda tidak memiliki izin untuk membuka halaman ini.</p>
    <a class="back-link mt-4 mb-0" href="{{ route('home') }}"><span aria-hidden="true">←</span> Beranda</a>
@endsection
