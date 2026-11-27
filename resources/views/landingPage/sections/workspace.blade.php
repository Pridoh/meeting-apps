<section class="workspace" id="notulen" aria-labelledby="judul-ruang-kerja">
    <div class="section-head" data-reveal="right">
        <p class="eyebrow">Ruang kerja rapat</p>
        <h2 id="judul-ruang-kerja">Agenda, notulen, dan tugas dalam satu <em class="accent">layar</em>.</h2>
        <p>Buka satu halaman untuk cek topik yang dibahas, baca keputusan, dan lihat tugas yang masih berjalan.</p>
    </div>

    <div class="ws-frame" data-reveal="scale">
        <div class="ws-window" role="img" aria-label="Pratinjau ruang kerja rapat: agenda, notulen hasil AI, dan daftar tugas">
            <div class="ws-col" data-reveal="up">
                <h4>Agenda · {{ $meeting['title'] }}</h4>
                <ol class="ws-agenda">
                    @foreach ($meeting['agenda'] as $item)
                        <li><time>{{ $item['time'] }}</time> {{ $item['topic'] }}</li>
                    @endforeach
                </ol>
                <div class="ws-note">
                    <span>Catatan awal</span>
                    Prototipe onboarding masih perlu uji internal sebelum rilis.
                </div>
            </div>

            <div class="ws-col" data-reveal="up">
                <h4>Notulen · hasil tinjauan</h4>
                <div class="mini-card">
                    <p class="mini-head"><x-icon name="zap" size="13" /> Ringkasan</p>
                    <div class="mini-line"></div>
                    <div class="mini-line"></div>
                    <div class="mini-line is-short"></div>
                </div>
                <div class="ws-note">
                    <span>Keputusan</span>
                    {{ $meeting['decision'] }}
                </div>
                <div class="mini-tags">
                    <span class="mini-tag">Final</span>
                    <span class="mini-tag">Ditinjau penyelenggara</span>
                </div>
            </div>

            <div class="ws-col" data-reveal="up">
                <h4>Tindak lanjut</h4>
                @foreach ($meeting['tasks'] as $task)
                    <div class="ws-task">
                        <strong>{{ $task['title'] }}</strong>
                        <small>{{ $task['owner'] }} · {{ $task['due'] }}</small>
                        <span class="badge {{ $task['status'] === 'Berjalan' ? 'badge--info' : 'badge--wait' }}">{{ $task['status'] }}</span>
                    </div>
                @endforeach
                <div class="ws-task">
                    <strong>Kirim ringkasan ke peserta</strong>
                    <small>Belum ditugaskan</small>
                    <span class="badge badge--wait">Menunggu</span>
                </div>
            </div>
        </div>
    </div>
</section>
