# Fase 1 — uji manual lulus, Fase 2 disetujui

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
