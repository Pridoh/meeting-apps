@extends('layouts.guest')

@section('title', 'Undangan tidak berlaku')

@section('content')
    <h1 class="page-title">Undangan tidak berlaku</h1>
    <p class="intro-text">Minta Organizer mengirim undangan baru ke email Anda.</p>
    <a class="back-link mt-4 mb-0" href="{{ route('home') }}"><span aria-hidden="true">←</span> Beranda</a>
@endsection
