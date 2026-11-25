# Keputusan teknis

A1–A7 dan keputusan lain di PRD final §3 sudah mengikat. PRD final memegang otoritas atas keputusan produk. Temuan dan opsi di AUDIT.md belum mengubahnya. Pertanyaan ada di OPEN_QUESTIONS.md.

| Pemilik | Batas modul dan kontrak |
|---|---|
| Meetings | Menyimpan meeting, agenda, status, versi jadwal dan soft delete. Event perubahan membawa identitas meeting, versi dan waktu efektif. Participation dan Rooms menangani dampak; operasi yang harus atomik selesai dalam transaksi yang sama. |
| Participation | Memiliki keanggotaan sejak Fase 1, undangan sejak Fase 2 dan pilihan serta snapshot sejak Fase 3. Menyediakan keanggotaan untuk pemeriksaan assignee dan hitungan terkini untuk Rooms. |
| Notes | Menyimpan draf dan review, memvalidasi hasil AiService, mengunci baris notulen saat finalisasi. Membuat keputusan dan tugas melalui satu transaksi. |
| ActionItems | Mengelola penanggung jawab dan progres tugas operasional. Isi notulen final berasal dari data review yang dibekukan. |
| Rooms | Memiliki katalog, reviewer, pengajuan, lampiran privat dan reservasi. Persetujuan memeriksa ulang versi serta interval sambil memegang kunci baris ruang. |
| Bersama | User, sesi, layout dan komponen Blade. Route modul dimuat oleh routes/web.php. Policies menilai hubungan terhadap objek pada tiap request. |

Fase 0 memakai PHP 8.5 Herd Lite dengan driver PDO MySQL/SQLite. Gunakan MySQL untuk pengujian integritas dan konkurensi. Untuk undangan gunakan Mailpit sebagai pilihan dev agar tautan bertoken tidak bocor ke log aplikasi. Hindari menyimpan token terbuka di payload antrean. Verifikasi kedua jalur sebelum Fase 2 selesai.

## Fase 0 disetujui dan diterapkan

- Buat rooms di fondasi agar seeder berfungsi dan meetings bisa memakai FK pada Fase 1. Nama dan lokasi bersama-sama menjadi identitas ruang pada seeder.
- Tombol utama menggunakan Dark Terracotta dengan teks putih. Palet dikelola di tailwind.config.js dan dimuat dari CSS Tailwind v4.
- GET/POST /login, GET /dashboard privat, POST /logout. Batasi lima kegagalan per pasangan email/IP selama 60 detik dengan cache database; kunci pendek mencegah dua request membaca kuota yang sama secara bersamaan. Registrasi dan verifikasi email masuk Fase 2.
- Gunakan database aplikasi rapatin dan database uji rapatin_phase0_test yang terpisah di container MySQL 8.4 khusus proyek. Seeder hanya membuat contoh pada lingkungan lokal dan testing, serta mempertahankan akun yang sudah ada.
- Pasang Boost dev dan gunakan versi patch kompatibel untuk paket yang terkena advisory pada lockfile awal. Delapan advisory awal menjadi nol sesudah pembaruan.

## Fase 1

- Implementasi mengikuti §14 untuk provider Gemini dan Fake. Aplikasi lokal memakai Fake supaya latihan UAS tidak membutuhkan kuota atau mengirim isi meeting. Untuk Gemini, isi GEMINI_API_KEY dan GEMINI_MODEL di environment serta pilih RAPATIN_AI_PROVIDER=gemini.
- Anggaran 30 detik berlaku untuk pemrosesan provider beserta satu retry jika format jawaban salah. Respons gagal mempertahankan draf dan review.
- Kolom teknis meeting_notes.revision menjaga agar tab lama atau hasil AI yang terlambat tidak menimpa perubahan baru. Perubahan isi draf menghapus hasil AI dan review lama. Finalisasi memeriksa kembali keanggotaan assignee saat memegang kunci baris meeting.

## Fase 2

- Hash SHA-256 mengidentifikasi token acak. Tautan membawa token di fragmen URL dan browser menukarnya melalui POST untuk sesi undangan. Penerimaan dan penolakan memakai akun dengan email tujuan yang telah diverifikasi.
- Kirim ulang merotasi token pada undangan yang sama. Kuota harian memakai zona waktu meeting. Undangan terminal dapat diganti dengan undangan baru. Undangan pending dapat dicabut langsung. Untuk anggota accepted, Organizer memakai Hapus peserta dengan konfirmasi dampak, yang juga mencabut undangannya.
- Database queue mengantrekan job terenkripsi setelah transaksi berhasil. SMTP lokal menuju Mailpit di port 1026, kotak masuk di port 8026. Pengiriman gagal dicatat pada upaya tersebut. Anggota lama tetap dapat mempunyai invitation_id kosong.
