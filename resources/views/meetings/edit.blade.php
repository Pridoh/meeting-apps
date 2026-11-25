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
            @if ($meeting->room_status === 'confirmed')
                <fieldset class="grid gap-2 rounded-lg border border-line bg-cream/50 p-4">
                    <legend class="px-2 font-semibold text-terracotta">Reservasi terkonfirmasi</legend>
                    <p class="text-sm text-muted">Reservasi akan dilepas jika waktu atau mode meeting berubah. Ajukan ruang kembali setelah menyimpan perubahan.</p>
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="confirm_room_release" value="1" class="mt-1 accent-terracotta">
                        Saya setuju melepas reservasi jika jadwal atau mode berubah.
                    </label>
                    @error('confirm_room_release')<p role="alert" class="form-error">{{ $message }}</p>@enderror
                </fieldset>
            @endif
            @include('meetings.form', ['cancelUrl' => route('meetings.show', $meeting)])
        </form>
    </x-card>
@endsection
