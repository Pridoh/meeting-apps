# Fase 4B — siap diuji mahasiswa

Antrean peninjauan dibatasi per organisasi dan penugasan ruang. AC-09–12, AC-17, AC-21–22 diverifikasi. SQLite dan MySQL masing-masing lulus 40 tes dan 587 assertion. Dua proses MySQL yang menyetujui slot identik menghasilkan satu reservasi aktif. Pengajuan kedua tetap menunggu dengan pesan slot terisi.

Chromium memverifikasi pengajuan manajer JJ 303, persetujuan penugasan, unggahan 3 MB, unduhan privat, persetujuan ruang, dan lokasi meeting pada empat lebar layar. Build Vite dan kompilasi Blade lulus. Uji manual mahasiswa dan checkpoint Fase 4B menunggu persetujuan sebelum Fase 5.

# Fase 4A — siap diuji mahasiswa

ORG-01–05 disetujui. Organisasi, kode bergabung, gedung, katalog, dan pengajuan manajer siap diuji. Verifikasi SQLite dan MySQL masing-masing lulus 37 tes dan 548 assertion. Chromium memeriksa katalog PENS pada 320, 390, 834 dan 1440 px. Build Vite dan kompilasi Blade lulus.

Migrasi dan seeder lokal memindahkan dua ruang demo tanpa mengganti ID. Satu pengajuan, satu reservasi, dan dua penugasan peninjau tetap tersimpan. Katalog PENS berisi lima ruang contoh di Gedung SAW dan Gedung D3, dengan kapasitas simulasi 30 kursi per ruang.

Pemilik proyek menyetujui kelanjutan ke Fase 4B. Checkpoint `phase-4a` (`025a8ee`) mencakup katalog dan integrasi awal pengajuan ruang.

# Fase 4 — siap diuji mahasiswa

Katalog ruang, pengajuan berversi, lampiran privat, antrean peninjauan, dan pelepasan reservasi tersedia. AC-09–12 dan AC-17 tercakup. SQLite dan MySQL masing-masing lulus 34 tes dan 506 assertion. Dua persetujuan MySQL dari proses berbeda menghasilkan satu reservasi aktif. Permintaan lainnya tetap menunggu dan menerima pesan bahwa slot sudah terisi.

Chromium memverifikasi pencarian, unggahan 3 MB, unduhan sebagai peninjau, persetujuan, lokasi meeting, dan tampilan pada 320, 390, 834, serta 1440 px. Migrasi aditif serta seeder peninjau berhasil di database pengembangan. Build Vite dan kompilasi Blade lulus.

Uji manual mahasiswa dan checkpoint Fase 4 menunggu persetujuan. Checkpoint sebelumnya adalah `phase-3` (`4c414cd`).

# Fase 3 — uji manual lulus

AC-05–08 lulus. SQLite dan MySQL masing-masing lulus 30 tes dengan 442 assertion. Dua proses MySQL menyimpan pilihan yang bertumpuk untuk akun yang sama. Kedua permintaan selesai dan hanya satu pilihan hadir yang tersisa. Uji waktu terkendali memastikan pilihan tepat H-1 masuk rekap dan hitungan 12/5/3 tetap sama setelah angka terkini menjadi 13/4/3.

Migrasi aditif berhasil di database pengembangan. Scheduler mendaftarkan `rapatin:snapshot-h1` setiap lima menit. Chromium memeriksa halaman Kehadiran dan Rekap kehadiran pada 390, 834 dan 1440 px, termasuk pengiriman lewat keyboard dan penolakan akses rekap untuk peserta.

Mahasiswa menyelesaikan uji manual Fase 3 dan menyetujui Fase 4. Checkpoint mencakup Fase 2, Fase 3, dan pembaruan antarmuka setelah pengujian.

# Fase 2 — uji manual dikonfirmasi

25 November 2026. Fase 1 lulus uji manual dan dicatat pada tag phase-1 (4a19509). Fase 2 mengganti penambahan anggota langsung dengan undangan email. Suite SQLite dan MySQL masing-masing lulus 26 tes dan 352 assertion, termasuk kegagalan pengiriman, kirim ulang, identitas terverifikasi, respons berulang, pencabutan serta kedaluwarsa. Build Vite dan kompilasi Blade berhasil.

Chromium membuka email undangan di Mailpit, mendaftarkan akun baru, mengikuti email verifikasi, kembali ke undangan, menerima, dan membuka meeting privat. Konfigurasi demo memakai worker database dan SMTP lokal. Petunjuk uji manual terdapat di LEARNING.md.

## Fase 1 — uji manual lulus, Fase 2 disetujui

Checkpoint Fase 0: tag `phase-0`, commit `f041be7`. Mahasiswa mengonfirmasi uji manual lulus.

## Verifikasi Fase 1

- AC-01 dan AC-13–16 sudah tercakup dalam suite SQLite dan MySQL. Database pengembangan rapatin sudah dimigrasikan tanpa reset.
- Dua proses PHP dengan koneksi MySQL terpisah mencoba finalisasi notulen yang sama. Hasilnya satu keputusan dan satu tugas.
- Chromium menelusuri dashboard → buat meeting → tambah peserta dan agenda → simpan draf → proses Fake → ubah review → finalisasi. Peserta memperbarui tugasnya dan membuka notulen final di layar 390px.
- Pengukuran lokal controller dashboard beserta render Blade, fixture MySQL 50 meeting dan 500 tugas: 22 ms untuk Organizer, 11 ms untuk Participant. Angka ini tidak mencakup jaringan atau rendering browser.
- Panggilan Gemini diuji dengan respons HTTP simulasi termasuk data tak valid dan kegagalan koneksi. Uji live membutuhkan model dan API key; mode lokal sengaja memakai Fake.

- Regresi akhir setelah catatan belajar: SQLite dan MySQL masing-masing lulus 23 tes dengan 310 assertion. Dua tes privasi memeriksa tugas yang terlihat peserta dan memastikan teks draf tak muncul dalam exception.
- Pint, Vite build, Blade view:cache dan git diff --check lulus.

## Hasil Fase 0

24 November 2026. Fondasi selesai diimplementasikan. Login/logout, dashboard privat, lima komponen Blade, katalog ruang awal dan dua akun contoh berjalan di lingkungan lokal. Aplikasi di http://127.0.0.1:8000/login. Gunakan organizer@rapatin.test dengan nilai RAPATIN_DEMO_PASSWORD dari .env lokal. Panduan menjalankan ulang, alur kode dan latihan ada di LEARNING.md.

## Hasil pemeriksaan

| Pemeriksaan | Hasil |
|---|---|
| Bootstrap | PHP 8.5.13 Herd Lite, Composer 2.8.12, Laravel 13.43.0, Boost 2.10.4. Aturan proyek tetap ada setelah generator Boost dijalankan. |
| Database | MySQL 8.4.13 di localhost:33316, volume Docker khusus. Empat migrasi berhasil di rapatin; seeder berhasil dijalankan dua kali. |
| Tes awal | Tujuh tes penerimaan baru gagal sebelum backend tersedia. Ditambah satu regresi setelah QA menemukan celah pada percobaan login bersamaan. |
| Regresi SQLite | php artisan test — 10 tes, 116 assertion lulus. |
| Regresi MySQL | DB_CONNECTION=mysql DB_DATABASE=rapatin_phase0_test CACHE_STORE=database php artisan test — 10 tes, 116 assertion lulus. Database uji khusus diperlukan karena runner dapat membuat ulang tabel. |
| Chromium | 390, 834, 1440 px. Tamu dialihkan, pesan login salah terbaca, password dikosongkan, login benar berhasil, dialog mendukung keyboard dan Escape, cookie sesi lama ditolak sesudah logout. POST tanpa token CSRF mendapat 419. |
| Build dan kualitas | Build Vite, kompilasi Blade, Pint, composer validate dan pemeriksaan platform lulus. Composer audit melaporkan nol advisory setelah pembaruan paket. Vite memberi peringatan optimasi font opsional. |
| Readability | Agent Learning menelusuri route, Form Request, controller, middleware dan seeder. Penjelasan cache lock, rotasi sesi, serta sifat firstOrCreate sudah tersedia di LEARNING.md. |

Mahasiswa mengonfirmasi uji manual Fase 0 lulus dan menyetujui Fase 1.
