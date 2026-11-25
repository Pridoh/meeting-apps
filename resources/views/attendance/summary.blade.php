@extends('layouts.workspace')

@section('title', 'Rekap kehadiran')

@section('content')
    <header class="workspace-heading">
        <a class="back-link" href="{{ route('meetings.show', $meeting) }}">Kembali ke meeting</a>
        <h1 class="page-title">Rekap kehadiran</h1>
        <p class="intro-text wrap-anywhere">{{ $meeting->title }}</p>
    </header>

    <x-card aria-labelledby="consumption-heading">
        <h2 id="consumption-heading" class="section-title">Estimasi konsumsi</h2>
        <p class="mt-2 text-sm text-muted">
            @if ($snapshot)
                Rekap H-1 dibekukan pada tenggat
                {{ \Carbon\CarbonImmutable::parse($snapshot->deadline_at, 'UTC')->timezone($meeting->timezone)->locale('id')->translatedFormat('d M Y, H.i') }}
                {{ $meeting->timezone }}. Perubahan sesudahnya terlihat di angka terkini.
            @elseif ($deadlineAvailable)
                Rekap H-1 tersedia setelah tenggat 24 jam sebelum meeting.
            @else
                Rekap H-1 tidak tersedia karena versi jadwal ini dibuat setelah tenggat.
            @endif
        </p>

        <div class="mt-4 overflow-x-auto focus-visible:outline-2 focus-visible:outline-terracotta" role="region" aria-label="Tabel rekap kehadiran" tabindex="0">
            <table class="w-full border-collapse text-sm tabular-nums">
                <caption class="sr-only">Perbandingan angka kehadiran terkini dan rekap H-1</caption>
                <thead class="bg-cream text-terracotta">
                    <tr>
                        <th scope="col" class="px-2 py-3 text-start sm:px-3">Rencana hadir</th>
                        <th scope="col" class="whitespace-nowrap px-2 py-3 text-end sm:px-3">Terkini</th>
                        <th scope="col" class="whitespace-nowrap px-2 py-3 text-end sm:px-3">H-1</th>
                        <th scope="col" class="whitespace-nowrap px-2 py-3 text-end sm:px-3">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach (['confirmed_count' => 'Terkonfirmasi', 'uncertain_count' => 'Belum pasti', 'not_attending_count' => 'Tidak berencana hadir'] as $key => $label)
                        <tr>
                            <th scope="row" class="px-2 py-4 sm:px-3 text-start font-semibold">{{ $label }}</th>
                            <td class="px-2 py-4 sm:px-3 text-end text-base font-semibold text-terracotta">{{ $current[$key] }}</td>
                            <td class="px-2 py-4 sm:px-3 text-end">{{ $snapshot?->{$key} ?? '—' }}</td>
                            <td class="px-2 py-4 sm:px-3 text-end">{{ $snapshot ? sprintf('%+d', $current[$key] - $snapshot->{$key}) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($snapshot)
            <p class="mt-4 text-sm text-muted">{{ $snapshot->unchosen_count }} dari kategori belum pasti belum memilih sampai H-1.</p>
        @endif
    </x-card>
@endsection
