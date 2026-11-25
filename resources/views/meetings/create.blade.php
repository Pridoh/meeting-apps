@extends('layouts.workspace')

@section('title', 'Buat meeting')

@section('content')
    <header class="workspace-heading">
        <a class="back-link" href="{{ route('meetings.index') }}">← Meeting</a>
        <h1 class="page-title">Buat meeting</h1>
    </header>

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('meetings.store') }}" class="grid gap-5">
            @csrf
            @include('meetings.form', ['meeting' => null, 'cancelUrl' => route('meetings.index')])
        </form>
    </x-card>
@endsection
