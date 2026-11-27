<section class="hero" aria-labelledby="judul-rapatin">
    <div class="hero-copy" data-reveal="blur">
        <h1 id="judul-rapatin">Smarter meetings,<br>Better <em class="accent">outcomes</em>.</h1>
        <p>Siapkan agenda dan kirim undangan. Peserta bisa memilih rapat saat jadwal bentrok. Setelah rapat, rapikan notulen dengan bantuan AI dan pantau tugas tim.</p>
        <div class="hero-actions">
            <x-landing.button href="#alur">Lihat alur rapat</x-landing.button>
            <span class="hero-note"><x-icon name="check-circle" size="15" /> Tanpa spreadsheet dan grup chat terpisah</span>
        </div>
    </div>

    <div class="hero-stage" data-reveal="scale">
        <div class="app-window" role="img" aria-label="Pratinjau dashboard Rapatin: agenda, rekap kehadiran H-1, notulen, dan tugas rapat tim produk">
            <div class="win-bar">
                <span class="win-dots" aria-hidden="true"></span>
                <span class="win-url">rapatin.app/rapat/tim-produk</span>
            </div>

            <div class="app-body">
                <aside class="app-side" aria-hidden="true">
                    <p class="app-side-title"><span class="avatar">R</span> Rapatin.</p>
                    <span class="side-item"><x-icon name="grid" size="14" /> Dashboard</span>
                    <span class="side-item is-active"><x-icon name="calendar" size="14" /> Rapat</span>
                    <span class="side-item"><x-icon name="list" size="14" /> Agenda</span>
                    <span class="side-item"><x-icon name="mail" size="14" /> Undangan</span>
                    <span class="side-item"><x-icon name="map-pin" size="14" /> Ruang</span>
                    <span class="side-item"><x-icon name="file-text" size="14" /> Notulen</span>
                    <span class="side-item"><x-icon name="check-square" size="14" /> Tugas</span>
                </aside>

                <div class="app-main">
                    <div class="app-head">
                        <div>
                            <h3>{{ $meeting['title'] }}</h3>
                            <small>Selasa, {{ $meeting['day'] }} {{ $meeting['month'] }} · {{ $meeting['time'] }}</small>
                        </div>
                        <span class="chip-btn">+ Undang peserta</span>
                    </div>

                    <div class="stat-row">
                        <div class="stat">
                            <b>8</b>
                            <span>Rapat bulan ini</span>
                            <em>+2 mendatang</em>
                        </div>
                        <div class="stat">
                            <b>12</b>
                            <span>Terkonfirmasi hadir</span>
                            <em>dari 20 undangan</em>
                        </div>
                        <div class="stat">
                            <b>5</b>
                            <span>Tugas aktif</span>
                            <em>3 selesai minggu ini</em>
                        </div>
                    </div>

                    <div class="app-grid">
                        <div class="panel">
                            <div class="panel-head"><span>Agenda rapat</span><span>3 topik</span></div>
                            @foreach ($meeting['agenda'] as $item)
                                <div class="row-item">
                                    <div class="date-chip"><b>{{ Str::substr($item['time'], 0, 2) }}</b>{{ Str::substr($item['time'], 3, 2) }}</div>
                                    <div>
                                        <strong>{{ $item['topic'] }}</strong>
                                        <small>{{ $item['minutes'] }} menit</small>
                                    </div>
                                    <span class="badge badge--info">Agenda</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="panel">
                            <div class="panel-head"><span>Rekap H-1</span><span>20 orang</span></div>
                            <div class="recap">
                                <div class="recap-row">
                                    <i></i>
                                    <div class="recap-bar"><span></span></div>
                                    <b>12</b>
                                </div>
                                <div class="recap-row">
                                    <i></i>
                                    <div class="recap-bar"><span></span></div>
                                    <b>5</b>
                                </div>
                                <div class="recap-row">
                                    <i></i>
                                    <div class="recap-bar"><span></span></div>
                                    <b>3</b>
                                </div>
                                <span class="float-sub">Terkonfirmasi · belum pasti · tidak hadir</span>
                            </div>
                        </div>
                    </div>

                    <div class="app-grid" data-reveal="up">
                        <div class="panel">
                            <div class="panel-head"><span>Tindak lanjut</span><span>{{ count($meeting['tasks']) }} tugas</span></div>
                            @foreach ($meeting['tasks'] as $task)
                                <div class="row-item">
                                    <span class="avatar">{{ Str::substr($task['owner'], 0, 1) }}</span>
                                    <div>
                                        <strong>{{ $task['title'] }}</strong>
                                        <small>{{ $task['owner'] }} · {{ $task['due'] }}</small>
                                    </div>
                                    <span class="badge {{ $task['status'] === 'Berjalan' ? 'badge--info' : 'badge--wait' }}">{{ $task['status'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="panel">
                            <div class="panel-head"><span>Notulen</span><span>Keputusan</span></div>
                            <p class="app-decision">{{ $meeting['decision'] }}</p>
                        </div>
                    </div>

                    <div class="app-grid">
                        <div class="panel">
                            <div class="panel-head"><span>Undangan</span><span>20 peserta</span></div>
                            <div class="row-item">
                                <span class="avatar">N</span>
                                <div>
                                    <strong>Aries Pratiarso</strong>
                                    <small>aries@timproduk.id</small>
                                </div>
                                <span class="badge badge--ok">Diterima</span>
                            </div>
                            <div class="row-item">
                                <span class="avatar">R</span>
                                <div>
                                    <strong>Raka Pratama</strong>
                                    <small>raka@timproduk.id</small>
                                </div>
                                <span class="badge badge--wait">Menunggu</span>
                            </div>
                            <div class="row-item">
                                <span class="avatar">S</span>
                                <div>
                                    <strong>Sinta Maharani</strong>
                                    <small>sinta@timproduk.id</small>
                                </div>
                                <span class="badge badge--ok">Diterima</span>
                            </div>
                        </div>

                        <div class="panel">
                            <div class="panel-head"><span>Ruang</span><span>Reservasi</span></div>
                            <div class="room-card">
                                <p class="room-name">{{ $meeting['room'] }}</p>
                                <p class="room-meta">Kapasitas 30 kursi · Organisasi PENS</p>
                                <div class="room-files">
                                    <span><x-icon name="file-text" size="13" /> surat-peminjaman.pdf</span>
                                    <span><x-icon name="file-text" size="13" /> rundown-rapat.pdf</span>
                                </div>
                                <span class="badge badge--ok">Disetujui</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="float-card float-card--left" aria-hidden="true">
            <p class="float-title"><x-icon name="check-circle" size="13" /> Undangan diterima</p>
            <div class="float-main"><span class="avatar">N</span> Nadia Putri</div>
            <p class="float-sub">Tim produk · 2 menit lalu</p>
        </div>

        <div class="float-card float-card--right" aria-hidden="true">
            <p class="float-title"><x-icon name="map-pin" size="13" /> Ruang rapat</p>
            <div class="float-main">{{ $meeting['room'] }}</div>
            <p class="float-sub">Reservasi aktif · <span class="badge badge--ok">Disetujui</span></p>
        </div>
    </div>
</section>
