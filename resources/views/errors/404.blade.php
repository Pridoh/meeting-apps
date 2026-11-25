@extends('layouts.guest')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    <h1 class="page-title">Halaman tidak ditemukan</h1>
    <p class="intro-text">Periksa kembali alamat halaman atau kembali ke beranda.</p>
    <a class="back-link mt-4 mb-0" href="{{ route('home') }}"><span aria-hidden="true">←</span> Beranda</a>
@endsection
