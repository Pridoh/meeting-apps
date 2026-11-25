@extends('layouts.workspace')

@section('title', 'Tugas')

@section('content')
    <header class="workspace-heading">
        <h1 class="page-title">Tugas</h1>
        <p class="intro-text">Pantau progres tugas Anda dan tugas pada meeting yang Anda kelola.</p>
    </header>

    <x-card>
        <ul class="divide-y divide-line">
            @forelse ($actionItems as $item)
                @include('action-items.row', ['meeting' => $item->meeting, 'showMeeting' => true])
            @empty
                <li class="empty-state">Belum ada tugas di halaman ini.</li>
            @endforelse
        </ul>

        @if ($actionItems->hasPages())
            <div class="mt-5">{{ $actionItems->links() }}</div>
        @endif
    </x-card>
@endsection
