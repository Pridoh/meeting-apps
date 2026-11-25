# RAPATIN

Aplikasi manajemen meeting untuk undangan, kehadiran, reservasi ruang organisasi, notulen, dan tindak lanjut.

## Menjalankan demo lokal

Gunakan PHP 8.5, Composer, Node.js dengan npm, MySQL, dan SMTP lokal (Mailpit). Pada mesin proyek ini, PHP dan Composer tersedia di `~/.config/herd-lite/bin`. Untuk instalasi baru, jalankan `composer install`, `npm ci`, lalu salin `.env.example` menjadi `.env` jika belum ada. Jalankan `php artisan key:generate` hanya untuk instalasi baru.

1. Isi koneksi database dan SMTP di `.env`. Samakan `APP_URL` dengan alamat server agar tautan email dapat dibuka. Untuk demo tanpa layanan AI eksternal, setel `RAPATIN_AI_PROVIDER=fake`. Tentukan `RAPATIN_DEMO_PASSWORD` sebelum seed pertama.
2. Jalankan perintah berikut pada database pengembangan lokal.

   ```bash
   php artisan migrate
   php artisan db:seed --class=PhaseOneMeetingSeeder
   npm run build
   composer run dev
   ```

3. Buka http://127.0.0.1:8000. Perintah terakhir menjalankan server, Vite, dan worker email. Di terminal kedua jalankan `php artisan schedule:work` untuk tenggat undangan dan rekap H-1.
4. Gunakan profil browser terpisah untuk `organizer@rapatin.test`, `peserta@rapatin.test`, dan `reviewer@rapatin.test`. Jika diminta verifikasi, kirim email dari halaman tersebut dan buka tautannya di profil yang sama. Pada mesin proyek ini Mailpit tersedia di http://127.0.0.1:8026.

Seeder menyediakan meeting **Evaluasi Persiapan Kegiatan**, peserta, dan katalog PENS. Menjalankan seed ulang mempertahankan data yang sudah ada. Jika jadwal contoh sudah lewat, buat meeting offline baru untuk demo reservasi.

## Verifikasi

Jalankan `npm run build` hingga selesai sebelum `php artisan test --compact`. Suite default memakai SQLite dalam memori. Untuk MySQL, arahkan pengujian ke database khusus tes karena runner dapat membuat ulang tabel.

Lihat [panduan belajar dan skenario demo](docs/LEARNING.md) serta [hasil QC](docs/PROGRESS.md).
