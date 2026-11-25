# Peta implementasi

Setiap baris menunggu persetujuan fase tersendiri. Urutan migrasi usulan mengikuti FK bagian 9. Keputusan yang perlu dikunci ada di DECISIONS.md.

| Fase | Urutan data dan hasil | Bukti untuk serah terima |
|---|---|---|
| 0 | Migrasi bawaan users/sessions, cache, jobs. Membuat rooms untuk seeder. Auth sesi, layout responsif, komponen Blade. | Langkah di bawah |
| 1 | meetings sesudah users dan rooms → agendas, meeting_participants (tanpa FK undangan dahulu), meeting_notes → decisions dan action_items. Fake dan Gemini melalui AiService. | AC-01, 13–16. Demo dari pembuatan meeting sampai pembaruan tugas pribadi |
| 2 | invitations → invitation_deliveries → tambah invitation_id nullable dan FK pada meeting_participants. Pertahankan anggota lama. Verifikasi email, pengiriman setelah commit, kedaluwarsa terjadwal. | AC-02–04 dan kotak surat uji |
| 3 | attendance_choices → attendance_snapshots. Riwayat dan mekanisme snapshot sesuai keputusan H-1. | AC-05–08, jam terkendali, pilihan konkuren di MySQL |
| 4A | organizations → organization_memberships dan buildings → FK gedung pada rooms → room_manager_applications. Pakai ulang room_reviewers. | AC-18–20, pemetaan data lama |
| 4B | Adaptasi room_requests, room_request_attachments, room_request_decisions dan room_reservations yang ada. | AC-09–12, AC-17, AC-21–22, dua koneksi MySQL independen |
| 5 | Integrasi semua modul, seed demo, responsif dan dokumentasi menjalankan aplikasi | AC-01–17, dashboard <2 detik dengan ±50 meeting/500 tugas, demo end-to-end |

## Fase 0 — pelaksanaan

- [x] Lead menetapkan kontrak route login GET/POST, logout POST dan satu halaman privat; menyepakati penempatan rooms dan tampilan tombol. Catat DB lokal dan DB pengujian yang terpisah.
- [x] Lead menyiapkan Composer dan PHP CLI dengan pdo_mysql, memeriksa persyaratan lock, memasang dependensi dari lockfile, memasang Boost sesuai bootstrap dan menggabungkan aturan yang dihasilkan. Periksa koneksi dan nama database sebelum migrasi. Hindari composer setup yang otomatis menjalankan migrate --force.
- [x] QA memiliki tests/. Siapkan tes tamu mengakses URL privat, login salah/benar, pembatasan percobaan, logout, dan akses kembali setelah logout.
- [x] Backend memiliki routes/modul auth, model, migration dan seeder. Pakai loader yang sudah ada. Validasi kredensial, regenerasi sesi setelah login, invalidasi sesi dan regenerasi token CSRF saat logout. Seed akun uji dan ruang secara berulang tanpa duplikasi.
- [x] Frontend memiliki Blade, CSS dan aset. Gunakan kontrak route dan nama input email/password yang disepakati. Hubungkan token Tailwind v4 ke config JS sesuai PRD; buat sidebar, button, card, input berlabel dan error, badge teks, modal dengan pengelolaan fokus. Uji pada ponsel, tablet, desktop dan keyboard.
- [x] QA menjalankan tes, build dan uji migrasi/seeder di DB uji. Learning menyederhanakan bagian yang menyulitkan pembaca dan menguraikan alur login di LEARNING.md; QA mengulang tes terdampak.
- [x] Lead mencatat hasil aktual di PROGRESS.md.
- [x] Uji manual login/logout dikonfirmasi lulus oleh mahasiswa. Fase 1 disetujui.

## Fase 1 — inti meeting, notulen dan tugas

- [x] AC-01, AC-16: dashboard sesuai relasi pengguna, CRUD meeting, agenda terurut, anggota terdaftar, transisi status manual dan otorisasi per objek.
- [x] AC-13: input teks atau TXT, provider Gemini dan Fake, validasi JSON, kegagalan tanpa kehilangan draf atau review.
- [x] AC-14, AC-15: review tersimpan, transaksi finalisasi idempoten, assignee anggota meeting, progres tugas terpisah dari notulen final.
- [x] Regresi SQLite/MySQL, alur browser Organizer dan Participant, review keamanan, materi belajar.
- [x] Mahasiswa mengonfirmasi uji manual Fase 1 lulus dan menyetujui Fase 2.

## Fase 2 — undangan

- [x] AC-02–04, migrasi kompatibel anggota lama, queue SMTP, pendaftaran/verifikasi, scheduler kedaluwarsa dan uji kotak surat lokal.
- [x] Mahasiswa mengonfirmasi uji manual Fase 2 lulus dan menyetujui Fase 3. Tag checkpoint Fase 2 masih terbuka.

## Fase 3 — kehadiran dan rekap H-1

- [x] AC-05–08, migrasi, scheduler, UI peserta dan rekap Organizer.
- [x] Regresi SQLite dan MySQL, dua pilihan MySQL konkuren, browser responsif, dan panduan belajar.
- [x] Mahasiswa menyelesaikan uji manual Fase 3 dan menyetujui Fase 4.

## Fase 4 awal — katalog dan peminjaman ruang

- [x] AC-09–12 dan AC-17. Katalog, formulir, versi pengajuan, berkas privat, dan antrean peninjau.
- [x] Regresi SQLite dan MySQL, dua persetujuan MySQL bersamaan, uji browser, dan panduan belajar.
- [ ] Uji manual model organisasi dan checkpoint dipindahkan ke Fase 4A–4B.

## Fase 4A — organisasi dan katalog

- [x] Kunci keputusan produk dan pemetaan katalog lama sesuai PRD §16.
- [x] Tambahkan organisasi, gabung melalui kode, gedung, katalog dan pengajuan manajer. Verifikasi AC-18–20.
- [x] Kelanjutan ke 4B disetujui dan checkpoint disimpan sebagai `phase-4a`.

## Fase 4B — reservasi dalam organisasi

- [x] Adaptasi reservasi, lampiran dan antrean dengan pemeriksaan organisasi serta penugasan ruang. Pertahankan aturan versi dan konkurensi.
- [x] Ulangi AC-09–12 dan AC-17, tambah AC-21–22 dan regresi meeting.
- [ ] Uji manual dan checkpoint sebelum Fase 5.
