@extends('layouts.app')

@section('title', 'Ruang kerja meeting')

@section('body')
    <header class="pitch-header">
        <x-brand :href="route('home')" light />
        <span class="pitch-edition" lang="en">PRODUCT CONCEPT &nbsp; / &nbsp; 2026</span>
        <a class="button button--secondary" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">{{ auth()->check() ? 'Dashboard' : 'Masuk' }}</a>
    </header>

    <main id="konten-utama" tabindex="-1">
        <section class="pitch-cover slide cover" aria-label="RAPATIN">
            <div class="cover-copy">
                <div class="eyebrow">RAPATIN.</div>
                <h1 lang="en">SMARTER MEETINGS.<br><em>BETTER OUTCOMES.</em></h1>
                <p class="intro">Pengembangan Sistem Manajemen Meeting Berbasis Web dengan Integrasi AI untuk Otomatisasi Notulensi dan Pemantauan Tindak Lanjut.</p>
                <div class="cover-actions">
                    <a class="primary" href="#features">Kenali RAPATIN</a>
                    @guest
                        <a class="button button--secondary" href="{{ route('register') }}" lang="en">REGISTER</a>
                    @endguest
                </div>
                <div class="cover-credit"><span>Final Project Workshop Teknologi Web dan Aplikasi</span>
                    <p>Kelompok 1<br>Nixon Kenji Susanto - 2426600014<br>Ilham Frido Bagaskara - 2426600012<br>Nurhadi Firni Hadinullah - 2426600025</p></div>
            </div>
            <div class="hero-art">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <div class="floating-note"><span class="note-dot"></span> Hasil rapat, lebih rapi.</div>
                <div class="paper paper-back"></div>
                <div class="paper">
                    <div class="paper-top"><span class="paper-logo">r.</span><span>MEETING NOTES<br>PRODUCT SYNC</span><span>✳</span></div>
                    <h2>Ide yang dibahas.<br>Aksi yang jelas.</h2>
                    <div class="paper-rule"></div>
                    <small>RINGKASAN MEETING</small>
                    <p>Prioritas tim disepakati.<br>Langkah berikutnya sudah siap.</p>
                    <div class="paper-task"><span>✓</span> Finalisasi desain dashboard</div>
                    <div class="paper-task"><span>✓</span> Review alur pengguna</div>
                    <div class="paper-task"><span>✓</span> Siapkan demo produk</div>
                    <div class="paper-bottom">TERSTRUKTUR DENGAN RAPATIN <span>✦</span></div>
                </div>
                <div class="ai-sticker"><span>✦</span>
                    <div>AI-assisted minutes<small>Catatan jadi keputusan.</small></div>
                </div>
            </div>
        </section>
        <nav class="pitch-navigation" aria-label="Bagian beranda">
            <a href="#features">Fitur</a>
            <a href="#how-it-works">Cara kerja</a>
            <a href="#ai-assistant">Notulen AI</a>
        </nav>

        <section id="features" class="border-y border-line bg-soft py-12 sm:py-18" aria-labelledby="fitur-heading">
            <div class="landing-container">
                <h2 id="fitur-heading" class="landing-section-title">Rapat, notulen, dan tugas dalam satu ruang kerja</h2>
                <dl class="mt-7 grid gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        'Kelola meeting' => 'Atur waktu dan lokasi, lalu kirim undangan ke peserta.',
                        'Susun agenda' => 'Urutkan topik pembahasan sebelum rapat dimulai.',
                        'Ringkas dengan AI' => 'Susun ringkasan dan draf keputusan dari catatan tertulis.',
                        'Pantau tugas' => 'Tetapkan penanggung jawab dan ikuti progres tindak lanjut.',
                    ] as $label => $description)
                        <div class="border-t border-line pt-4">
                            <dt class="font-semibold text-terracotta">{{ $label }}</dt>
                            <dd class="mt-2 text-sm leading-relaxed text-muted">{{ $description }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        <section id="how-it-works" class="py-12 sm:py-18" aria-labelledby="workflow-heading">
            <div class="landing-container">
                <h2 id="workflow-heading" class="landing-section-title">Dari jadwal hingga tindak lanjut</h2>
                <ol class="mt-7 list-decimal space-y-4 pl-7 text-terracotta marker:font-semibold lg:grid lg:grid-cols-3 lg:gap-x-10 lg:space-y-0">
                    @foreach ([
                        'Jadwalkan rapat' => 'Isi judul, waktu, dan agenda rapat. Undang peserta melalui email.',
                        'Simpan catatan' => 'Tulis catatan rapat atau unggah berkas teks untuk membuat draf.',
                        'Tinjau dan finalisasi' => 'Periksa hasil AI, tetapkan tugas, lalu simpan notulen final.',
                    ] as $label => $description)
                        <li class="pl-1">
                            <h3 class="font-semibold">{{ $label }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">{{ $description }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section id="ai-assistant" class="border-y border-line bg-soft py-12 sm:py-18" aria-labelledby="ai-heading">
            <div class="landing-container grid items-start gap-7 lg:grid-cols-[minmax(0,4fr)_minmax(0,5fr)] lg:gap-12">
                <div>
                    <h2 id="ai-heading" class="landing-section-title">Tinjau hasil AI sebelum finalisasi</h2>
                    <p class="mt-3 max-w-lg leading-relaxed text-muted">
                        AI membantu menyusun draf dari catatan rapat tertulis. Anda bisa memeriksa dan mengubah ringkasan, keputusan, serta saran tindak lanjut sebelum mengunci notulen.
                    </p>
                </div>
                <x-card class="bg-cream/35" aria-labelledby="contoh-notulen">
                    <h3 id="contoh-notulen" class="section-title">Contoh draf notulen</h3>
                    <dl class="mt-3 grid gap-3 text-sm leading-relaxed">
                        <div>
                            <dt class="font-semibold text-terracotta">Ringkasan</dt>
                            <dd>Tim memeriksa kesiapan ruangan dan perlengkapan kegiatan.</dd>
                        </div>
                        <div class="border-t border-line pt-3">
                            <dt class="font-semibold text-terracotta">Keputusan</dt>
                            <dd>Gladi bersih dilakukan sehari sebelum kegiatan.</dd>
                        </div>
                        <div class="border-t border-line pt-3">
                            <dt class="font-semibold text-terracotta">Tindak lanjut</dt>
                            <dd>Perbarui daftar perlengkapan untuk gladi bersih.</dd>
                        </div>
                    </dl>
                </x-card>
            </div>
        </section>
    </main>

    <footer class="landing-nav">
        <div class="landing-container flex flex-wrap items-center justify-between gap-3 py-5">
            <x-brand :href="route('home')" light />
            <p class="text-sm text-muted">© 2026 RAPATIN</p>
        </div>
    </footer>
@endsection
