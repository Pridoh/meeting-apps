@extends('layouts.workspace')

@section('title', 'Meeting')

@section('content')
    <header class="workspace-heading flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Meeting</h1>
            <p class="intro-text">Meeting yang Anda kelola atau ikuti.</p>
        </div>
        <a class="button button--primary" href="{{ route('meetings.create') }}">Buat meeting</a>
    </header>

    <x-card>
        @if ($meetings->isEmpty())
            <p class="text-muted">Belum ada meeting di halaman ini.</p>
        @else
            <ul class="divide-y divide-line">
                @foreach ($meetings as $meeting)
                    <x-meeting-row :meeting="$meeting" show-organizer />
                @endforeach
            </ul>
        @endif
        @if ($meetings->hasPages())
            <div class="mt-5">{{ $meetings->links() }}</div>
        @endif
    </x-card>
@endsection
