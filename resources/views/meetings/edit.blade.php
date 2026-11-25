@extends('layouts.workspace')

@section('title', 'Ubah meeting')

@section('content')
    <header class="workspace-heading">
        <a class="back-link" href="{{ route('meetings.show', $meeting) }}">← Detail meeting</a>
        <h1 class="page-title">Ubah meeting</h1>
    </header>

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('meetings.update', $meeting) }}" class="grid gap-5">
            @csrf
            @method('PUT')
            @include('meetings.form', ['cancelUrl' => route('meetings.show', $meeting)])
        </form>
    </x-card>
@endsection
