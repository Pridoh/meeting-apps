<!DOCTYPE html>
<html lang="id" class="landing-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Siapkan agenda dan undangan di Rapatin. Peserta bisa memilih rapat saat jadwal bentrok, sementara tim bisa merapikan notulen dengan AI dan memantau tugas.">
    <title>Rapatin | Kelola rapat dan tindak lanjut tim</title>
    <x-theme-init />
    <link rel="icon" type="image/svg+xml" href="{{ asset('image/logo_rapatin.svg') }}">
    <x-rapatin-fonts />
    @vite(['resources/css/app.css', 'resources/js/landing.js'])
</head>
<body class="rapatin">
    @php
        $meeting = [
            'title' => 'Sinkronisasi produk',
            'day' => '12',
            'month' => 'Mei',
            'time' => '09.00–10.00 WIB',
            'room' => 'Gedung SAW · 04 06',
            'agenda' => [
                ['time' => '09:00', 'topic' => 'Tinjau progres', 'minutes' => 15],
                ['time' => '09:15', 'topic' => 'Evaluasi prototipe', 'minutes' => 25],
                ['time' => '09:40', 'topic' => 'Bagi tugas', 'minutes' => 20],
            ],
            'decision' => 'Uji alur onboarding dengan tim internal.',
            'tasks' => [
                ['title' => 'Siapkan skenario uji', 'owner' => 'Nadia', 'due' => '15 Mei', 'status' => 'Berjalan'],
                ['title' => 'Jadwalkan sesi uji', 'owner' => 'Raka', 'due' => '16 Mei', 'status' => 'Menunggu'],
            ],
        ];
    @endphp

    @include('landingPage.sections.navbar')

    <div class="scroll-ruler" aria-hidden="true">
        <span>0%</span>
    </div>

    <main id="atas">
        @include('landingPage.sections.hero')
        @include('landingPage.sections.highlights')
        @include('landingPage.sections.clarity')
        @include('landingPage.sections.workspace')
    </main>

    @include('landingPage.sections.footer')
</body>
</html>
