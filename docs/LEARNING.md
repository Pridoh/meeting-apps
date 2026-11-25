# Panduan belajar RAPATIN

Panduan ini mengajak anggota tim baru memahami proyek sebelum menambah fitur. Acuan kebutuhan adalah [PRD final](../.codex/PRD-RAPATIN-final.md); fitur di PRD adalah target, bukan bukti bahwa fitur sudah tersedia.

## Mulai dari alur pengguna

1. Baca bagian 3–6 PRD. Gambarkan alur Organizer membuat meeting dan agenda, mengundang peserta, lalu peserta merespons. Bedakan pengiriman email dari penerimaan undangan: email terkirim belum berarti penerima menjadi peserta.
2. Jelaskan bagaimana pilihan hadir menangani jadwal yang bertabrakan, kapan rekap H-1 diambil, dan apa yang terjadi jika jadwal berubah. Tenggat adalah 24 jam sebelum meeting paling awal dalam bentrok yang relevan; waktu disimpan UTC dan ditampilkan sesuai zona meeting (bagian 3).
3. Ikuti alur pengajuan ruang: pilih ruang, lampirkan dokumen, tunggu keputusan petugas berwenang. Persetujuan menghasilkan reservasi; pengajuan menunggu bukan kepastian ruang.
4. Ikuti notulen: masukan berupa catatan tertulis atau berkas teks, hasil AI diperiksa manusia sebelum finalisasi dan tugas dibuat. Perekaman audio dan transkripsi berada di luar MVP.
5. Baca acceptance criteria AC-01–AC-17 pada bagian 11. Untuk setiap alur, tulis kondisi awal, tindakan, hasil, serta kasus akses atau kegagalan yang harus ditolak.

## Menelusuri kode yang ada

Mulai dari `routes/web.php`: file ini mencari `Config/Routes.php` pada setiap direktori dalam `app/Modules`. Saat ini `app/Modules/Home/Config/Routes.php` mendefinisikan route GET `/` bernama `home` yang mengembalikan `resources/views/landingPage/index.blade.php`. Telusuri berkas tersebut untuk melihat tampilan saat ini. Nama dan klaim dalam tampilan merupakan isi halaman, bukan bukti integrasi atau fitur tersedia.

Baca `composer.json` untuk versi dan skrip proyek, lalu `tests/Feature/ExampleTest.php` untuk contoh uji yang memeriksa respons halaman utama. `app/Models/User.php` adalah titik awal memahami model pengguna. Bandingkan temuan kode dengan PRD sebelum menyatakan fitur tertentu selesai.

## Alur login Fase 0 dalam kode

1. `routes/web.php` memuat route tiap modul. `app/Modules/Auth/Config/Routes.php` menyediakan GET dan POST `/login` untuk tamu, serta GET `/dashboard` dan POST `/logout` untuk pengguna yang sudah login. Middleware `auth` mengarahkan tamu dari dashboard ke login.
2. Form di `resources/views/auth/login.blade.php` mengirim email, kata sandi, dan token CSRF. `LoginRequest` memeriksa email dan kata sandi wajib diisi serta format email.
3. `SessionController::store` mengubah email menjadi huruf kecil, lalu membuat kunci pembatas dari hash email dan alamat IP. Cache lock membuat percobaan untuk pasangan yang sama diperiksa bergiliran. Setelah lima kegagalan, percobaan berikutnya dibatasi selama satu menit; percobaan berhasil menghapus hitungan.
4. `Auth::attempt` memeriksa kredensial melalui Laravel. Pada Laravel 13, `SessionGuard::updateSession` merotasi ID sesi saat autentikasi berhasil. Pengguna diarahkan ke tujuan sebelumnya atau dashboard.
5. Saat logout, `SessionController::destroy` mengakhiri autentikasi, menginvalidasi sesi, membuat ulang token CSRF, dan mengarahkan ke login. `tests/Feature/AuthenticationTest.php` memperlihatkan skenario berhasil, gagal, batas percobaan, dan akses setelah logout.
6. `DatabaseSeeder` membuat akun dan ruang contoh hanya di lingkungan `local` atau `testing`. `firstOrCreate` mempertahankan data yang sudah ada, termasuk kata sandi akun demo; mengganti nilai di `.env` setelah akun dibuat tidak mengubah kata sandi akun tersebut.

## Menjalankan dan mencoba Fase 0

Langkah ini untuk lingkungan lokal yang sudah disiapkan. Jangan gunakan database produksi. Pastikan PHP dan Composer tersedia; pada mesin ini Herd Lite berada di `~/.config/herd-lite/bin`. Jika belum ada di PATH terminal, jalankan:

```bash
export PATH="$HOME/.config/herd-lite/bin:$PATH"
php -v
composer -V
```

1. Pastikan container MySQL lokal `rapatin-phase0-mysql` berjalan dengan `docker start rapatin-phase0-mysql` bila sedang berhenti. Periksa pengaturan koneksi pada `.env`; database pengembangan adalah `rapatin`, sedangkan `rapatin_phase0_test` disediakan terpisah untuk pengujian MySQL. Nilai dan kredensial lokal tidak perlu disalin ke dokumentasi.
2. Jika dependensi dan kunci aplikasi belum tersedia, siapkan sesuai konfigurasi lokal terlebih dahulu. Atur `RAPATIN_DEMO_PASSWORD` di `.env` sebelum seed pertama, lalu jalankan `php artisan migrate` dan `php artisan db:seed`. Seeder tidak menimpa akun yang sudah ada. Tanpa nilai itu, password akun baru dibuat acak.
3. Jalankan `npm run dev` dan, di terminal lain, `php artisan serve`. Buka alamat lokal yang ditampilkan Artisan. Untuk aset hasil produksi lokal, `npm run build` adalah alternatif Vite dev server.
4. Buka `/dashboard` sebagai tamu dan pastikan diarahkan ke `/login`. Coba login dengan kata sandi salah dan periksa pesan. Login dengan `organizer@rapatin.test` dan nilai `RAPATIN_DEMO_PASSWORD` yang dipakai saat akun dibuat; pastikan dashboard tampil. Logout, lalu buka `/dashboard` lagi dan pastikan kembali ke login.
5. Untuk memeriksa pembatas, lakukan lima percobaan salah pada email dan IP yang sama; percobaan berikutnya ditolak sementara. Tunggu satu menit sebelum mencoba lagi. Catat hasil nyata dan kondisi browser yang digunakan.
6. Jalankan `php artisan test` untuk tes otomatis. Secara default `phpunit.xml` memakai SQLite in-memory, sehingga hasilnya tidak membuktikan perilaku MySQL. Jika ingin menguji MySQL, konfigurasikan koneksi pengujian terpisah ke `rapatin_phase0_test` sebelum menjalankan tes; jangan mengarahkannya ke database pengembangan, karena tes dapat mengubah tabel.

## Latihan Fase 0 dan pertanyaan UAS

1. Telusuri POST `/login` dari route hingga `SessionController::store`. Apa peran `LoginRequest` dan mengapa kata sandi tidak boleh ditampilkan pada pesan error?
2. Mengapa akses langsung ke `/dashboard` harus diperiksa oleh middleware, walaupun tautan dashboard disembunyikan dari tamu?
3. Jelaskan apa yang terjadi setelah lima login salah, bagaimana email dan IP membentuk batasnya, dan mengapa cache lock diperlukan untuk request bersamaan.
4. Jelaskan peran `Auth::attempt`, rotasi ID sesi oleh `SessionGuard`, dan invalidasi sesi serta regenerasi token CSRF saat logout.
5. Mengapa `firstOrCreate` mencegah duplikasi saat seed diulang? Mengapa perubahan password demo di `.env` tidak otomatis mengganti akun yang telah dibuat?
6. Baca satu tes di `AuthenticationTest.php`. Tuliskan kondisi awal, request, dan hasil yang diperiksa. Apa perbedaan tes SQLite default dan uji integrasi pada MySQL?

## Latihan awal

1. **Peta request.** Tanpa mengubah kode, gambar jalur browser → `routes/web.php` → route modul Home → view. Jelaskan fungsi masing-masing dan tunjukkan lokasi definisi URL `/`.
2. **Kasus bentrok.** Pakai AC-05: A 09.00–10.00, B 09.30–10.30, C 10.00–11.00. Jelaskan mengapa A dan C bisa dipilih bersama, tetapi B menggantikan keduanya. Tentukan uji untuk dua respons bersamaan.
3. **Akses dan kegagalan.** Dari AC-03, AC-12, dan AC-13, tulis masing-masing satu skenario berhasil dan satu skenario ditolak atau gagal. Catat data yang harus tetap terlindungi ketika gagal.
4. **Dari PRD ke tugas.** Pilih satu acceptance criterion; buat daftar perubahan yang diperlukan pada route, validasi, penyimpanan, otorisasi, tampilan, dan pengujian. Gunakan keputusan yang sudah dikunci pada bagian 3; catat celah yang belum dijelaskan untuk ditanyakan.
5. **Baca tes.** Jelaskan apa yang dibuktikan tes halaman utama dan apa yang belum dibuktikannya. Usulkan satu tes yang akan gagal jika aturan bisnis pilihan Anda rusak.

Saat lingkungan proyek sudah siap, jalankan tes yang relevan menggunakan `php artisan test`; laporkan kegagalan dan prasyarat yang belum tersedia. Jangan memasukkan kredensial asli ke latihan atau repositori. Lihat [pemetaan skill](SKILLS.md) untuk memilih panduan kerja sesuai tugas.

## Hubungan data yang direncanakan

Skema lengkap dan nama kolom terdapat di bagian 9 PRD final. Ini rancangan target, bukan pernyataan bahwa seluruh tabel sudah dibuat.

- `users` memiliki banyak `meetings` melalui `meetings.user_id` sebagai Organizer. `meeting_participants` menghubungkan pengguna dan meeting; pasangan meeting–user unik.
- Satu meeting memiliki banyak `agendas`, `invitations`, `attendance_choices`, dan `attendance_snapshots`. `invitation_deliveries` merekam percobaan pengiriman untuk satu undangan; keanggotaan dapat menunjuk undangan asal.
- `rooms` memiliki reviewer melalui `room_reviewers`. `room_requests` menghubungkan meeting, pemohon, dan ruang; lampiran dan keputusan menunjuk pengajuan serta versi yang diperiksa. `room_reservations` menghubungkan ruang, pengajuan, dan meeting.
- Satu meeting memiliki maksimal satu `meeting_notes`, serta banyak `decisions` dan `action_items`. Penanggung jawab tugas boleh kosong; jika ada, harus anggota meeting yang sama.

## Pertanyaan latihan UAS

1. Apa beda primary key dan foreign key? Tunjukkan masing-masing pada `meetings` dan `agendas`.
2. Mengapa tabel `meeting_participants` memerlukan batas unik `(meeting_id, user_id)`? Berikan contoh akibat jika batas itu tidak ada.
3. Jelaskan alur Route → Middleware → Controller → Service → Model menggunakan contoh pembuatan meeting. Di mana validasi dan pemeriksaan izin dilakukan?
4. Mengapa pemeriksaan tombol yang disembunyikan tidak cukup untuk melindungi lampiran? Jelaskan pemeriksaan server saat URL diakses langsung.
5. Apa fungsi transaksi saat finalisasi notulen dan persetujuan ruang? Jelaskan risiko jika hanya sebagian perubahan berhasil.
6. Dengan dua permintaan persetujuan bersamaan untuk ruang yang sama, bagaimana kunci baris ruang dan pemeriksaan ulang mencegah dua reservasi aktif?
7. Jelaskan beda snapshot H-1 dan angka terkini setelah koreksi terlambat. Apa dampak perubahan jadwal terhadap `schedule_version`?
8. Mengapa hasil AI wajib divalidasi dan ditinjau sebelum finalisasi? Apa yang terjadi pada draf jika provider gagal?

## Urutan implementasi

Ikuti bagian 14 PRD final: fondasi (fase 0), inti meeting dan notulen (fase 1), undangan (fase 2), bentrok dan H-1 (fase 3), ruang (fase 4), lalu penyempurnaan (fase 5). Kerjakan satu fase pada satu waktu, tulis tes skenario fase terlebih dahulu, dan lanjutkan setelah Definition of Done terpenuhi serta manusia mencoba alurnya.

## Fase 1: alur meeting, notulen dan tugas

- Modul Meetings memetakan URL ke controller. Form Request memvalidasi input dan Policy membatasi aksi berdasarkan pemilik atau anggota meeting.
- MeetingController memakai MeetingService untuk perubahan status dan jadwal. SaveMeetingRequest mengubah waktu lokal sesuai zona meeting ke UTC saat disimpan. MeetingScheduleChanged mencatat versi saat waktu atau mode berubah.
- meeting_participants menghubungkan akun dengan meeting. Penambahan hanya menerima email akun terdaftar. Menghapus anggota memerlukan konfirmasi dampak, mencabut akses, dan mengosongkan penanggung jawab tugas aktifnya.
- Organizer menyimpan catatan di halaman notulen. NotesController meminta AiService. Konfigurasi lokal memilih FakeProvider agar demo tidak memerlukan API key. GeminiProvider mengirim judul, tanggal, deskripsi, agenda, peserta, dan draf. Jawaban JSON divalidasi, lalu Organizer menyimpan review yang telah diedit.
- Perubahan notulen menaikkan meeting_notes.revision di bawah kunci transaksi meeting. Form dari tab lama ditolak. Mengubah draf menghapus hasil AI dan review lama. Finalisasi memvalidasi penanggung jawab lagi, lalu menyimpan keputusan, tugas, waktu dan pelaku dalam transaksi yang sama.
- Isi notulen final dibaca dari review yang dibekukan. Participant hanya mendapat tugas miliknya. Organizer melihat semua tugas meeting. Perubahan status tugas tidak menulis ulang isi notulen.

### Mencoba demo dan latihan UAS

Seed lokal menyediakan organizer@rapatin.test dan peserta@rapatin.test. Password berasal dari RAPATIN_DEMO_PASSWORD saat pertama kali di-seed. Jalankan server seperti bagian Fase 0, lalu:

1. Masuk sebagai Organizer. Buat meeting offline dengan waktu mulai dan selesai yang sah.
2. Tambah email Participant dari akun demo. Tambahkan dua agenda dan ubah urutannya.
3. Buka notulen, simpan catatan, proses dengan Fake, edit ringkasan dan keputusan, tambahkan tugas untuk Participant, lalu simpan review.
4. Finalisasi notulen. Masuk sebagai Participant, buka daftar tugas, perbarui status tugasnya, lalu buka notulen final. Pastikan tugas akun lain tidak terlihat.
5. Buka URL meeting Organizer sebagai Participant lain. Pastikan server menolak akses.
6. Ubah draf sesudah review tersimpan. Review lama perlu diproses dan ditinjau ulang sebelum finalisasi.

Pertanyaan presentasi: mengapa waktu disimpan UTC bersama zona meeting? Mengapa Policy server tetap dibutuhkan saat tombol tidak ditampilkan? Bagaimana revision mencegah penyimpanan dari tab lama? Apa manfaat transaksi saat finalisasi? Mengapa Participant tidak dapat mengubah tugas orang lain? Bagaimana Fake membantu menguji alur AI tanpa layanan eksternal?

## Fase 2: dari undangan sampai menjadi anggota

1. MeetingParticipantController meneruskan email ke InvitationService. Service menormalkan email, mengunci baris meeting, lalu menyimpan undangan dan satu catatan pengiriman. Indeks unik active_email melindungi alamat pending atau accepted dari duplikasi.
2. Worker menjalankan SendInvitation setelah commit. Saat peserta membuka tautan, halaman undangan menampilkan jadwal dan Organizer. Membuka email belum membuat keanggotaan.
3. Penerima dapat masuk atau mendaftar. RegistrationController memeriksa tautan verifikasi bertanda tangan dan mengembalikan pengguna ke undangan. Akun dengan email berbeda harus berganti akun.
4. Saat Terima, InvitationService memeriksa kembali token, waktu dan email lalu mengubah status serta menambah anggota dalam satu transaksi. Tolak menyimpan respons tanpa menambah anggota. Menekan tombol yang sama lagi mempertahankan hasil pertama.

**Latihan manual.** Di http://127.0.0.1:8000 buat undangan untuk akun demo dan satu alamat baru. Buka http://127.0.0.1:8026 untuk membaca email. Gunakan jendela privat untuk mendaftar dengan alamat baru, lalu buka tautan verifikasi pada jendela yang sama. Terima dan periksa daftar peserta. Untuk undangan kedua, coba akun berbeda, kirim ulang setelah satu menit, lalu uji tautan lama dan pencabutan. Setelah restart jalankan container rapatin-mailpit dan rapatin-phase0-mysql, php artisan queue:work, php artisan schedule:work, serta server aplikasi.

**Pertanyaan UAS.** Apa perbedaan status pengiriman dan status respons? Mengapa data undangan dan keanggotaan perlu disimpan dalam satu transaksi saat diterima? Bagaimana tautan lama kehilangan akses setelah kirim ulang? Gunakan tests/Feature/InvitationTest.php untuk menelusuri kasusnya.

## Fase 3 — bentrok dan rekap H-1

- `AttendanceController` menerima pilihan dari halaman **Kehadiran**. Form Request memvalidasi pilihan dan versi jadwal. Service hanya memakai undangan yang terkait dengan email yang terverifikasi atau keanggotaan akun tersebut.
- `AttendanceService` menghitung irisan dengan dua perbandingan. Jam mulai A harus lebih awal dari jam selesai B dan jam mulai B harus lebih awal dari jam selesai A. Lalu service mengunci baris akun, memeriksa versi jadwal dan mencatat riwayat pilihan beserta perubahan jadwal yang bertumpuk dalam satu transaksi.
- `AttendanceRecap` menghitung tiap email satu kali. Scheduler menyimpan snapshot unik per meeting dan versi jadwal setelah tenggat. Pilihan tepat pada tenggat masuk rekap. Koreksi setelah tenggat memengaruhi angka terkini. Perubahan waktu atau mode menaikkan versi sehingga pilihan lama disimpan sebagai riwayat dan peserta diminta mengonfirmasi ulang.
- `attendance_choices` menghubungkan akun ke meeting beserta versi yang dipilih. `attendance_snapshots` menyimpan jumlah per kategori untuk satu versi meeting offline. Hanya Organizer pemilik meeting yang bisa membuka rekap.

**Coba di browser.** Buat dua meeting offline yang bertumpuk dan undang email terverifikasi yang sama ke keduanya. Buka **Kehadiran** sebagai penerima, pilih meeting pertama, lalu pilih meeting kedua. Periksa bahwa pilihan pertama berubah menjadi **Meeting lain dipilih**. Buka **Detail → Rekap kehadiran** sebagai Organizer. Coba URL rekap sebagai peserta dan pastikan akses ditolak. Untuk batas waktu tanpa menunggu 24 jam, jalankan `vendor/bin/phpunit tests/Feature/AttendanceTest.php`.

**Latihan UAS.** Tambahkan pertemuan C yang bersinggungan dengan B tetapi mulai tepat saat A selesai. Prediksi pilihan apa yang bertahan jika peserta memilih A, C, lalu B. Jelaskan mengapa kunci baris akun diperlukan pada dua permintaan bersamaan dan mengapa jumlah snapshot tidak berubah setelah koreksi terlambat.

## Fase 4 — peminjaman ruang

- `RoomBookingController` menampilkan katalog, formulir, dan antrean. Waktu pengajuan mengikuti jadwal meeting. Rekap kehadiran memberi jumlah terkonfirmasi sebagai referensi, sedangkan Organizer mengisi kebutuhan total termasuk panitia.
- `RoomBooking` menyimpan versi pengajuan, lampiran, dan keputusan. Berkas berada di penyimpanan privat dan hanya dikirim setelah `RoomRequestPolicy` memeriksa hubungan pemohon atau peninjau dengan pengajuan.
- Persetujuan mengunci baris meeting dan ruang, memeriksa versi pengajuan serta interval reservasi aktif, kemudian menyimpan keputusan dan reservasi dalam satu transaksi. Pengajuan menunggu tidak memblokir slot. Pertemuan yang mulai tepat saat reservasi lain selesai tidak bertumpuk.
- Menggeser waktu atau mode setelah persetujuan meminta konfirmasi, melepas reservasi lama, dan menutup pengajuan lama. Untuk pindah ruang, gunakan **Lepas reservasi** sebelum memilih ruang lain.

**Coba di browser.** Buat meeting offline yang akan datang, lalu buka **Ruang** atau **Cari dan ajukan ruang**. Pilih ruang, isi jumlah orang, dan unggah PDF atau gambar uji. Masuk dengan `reviewer@rapatin.test` di profil browser terpisah dan buka **Ruang → Antrean peninjauan**. Gunakan nilai `RAPATIN_DEMO_PASSWORD` seperti akun demo lain. Minta perbaikan, kirim ulang sebagai Organizer, lalu setujui versi terbaru sebagai peninjau. Periksa lokasi terkonfirmasi di detail meeting. Coba buka tautan lampiran dari akun peserta dan ubah jadwal meeting yang sudah disetujui.

**Latihan UAS.** Jelaskan mengapa kunci baris ruang mencegah dua reservasi bersamaan dan bagaimana versi pengajuan menjaga keputusan tetap terkait dengan lampiran yang ditinjau. Uji slot yang dimulai tepat saat reservasi sebelumnya selesai.

## Fase 4A — organisasi dan katalog ruang

`OrganizationController` menerima permintaan buat atau gabung organisasi. `OrganizationPolicy` memeriksa keanggotaan untuk melihat katalog dan kepemilikan untuk mengubahnya. Organisasi ruang ditentukan melalui gedung. Persetujuan pengajuan manajer mengubah status dan menulis ke `room_reviewers` dalam satu transaksi.

**Latihan.** Masuk sebagai Organizer Demo dan buka **Organisasi → POLITEKNIK ELEKTRONIKA NEGERI SURABAYA**. Salin kode gabung dari halaman pemilik. Buka profil browser terpisah sebagai peserta, gabung dengan kode, dan ajukan diri sebagai manajer salah satu ruang SAW. Kembali sebagai pemilik dan setujui. Pastikan akses kepemilikan tidak muncul pada akun peserta, dan status manajer hanya muncul pada ruang yang disetujui.

**Pertanyaan UAS.** Kenapa akses katalog perlu diperiksa lagi ketika URL diganti? Mengapa persetujuan dan penugasan harus satu transaksi?

## Fase 4B — reservasi dalam organisasi

`RoomBookingController` memilih organisasi dari keanggotaan pengguna dan memfilter katalog serta antrean. `RoomRequestPolicy` memeriksa pemohon atau hubungan peninjau dengan ruang dan organisasi pada saat detail atau lampiran dibuka. `RoomBooking` memeriksa versi pengajuan dan mengunci ruang sebelum menyimpan reservasi.

**Latihan.** Setujui permohonan manajer JJ 303 sebagai pemilik PENS. Buat meeting offline mendatang, pilih PENS dari **Ruang**, ajukan JJ 303 dengan lampiran uji, lalu setujui dari akun manajer melalui **Antrean peninjauan**. Coba buka pengajuan dari akun yang mengelola ruang berbeda. Jika akun tersebut menjadi peserta meeting, periksa bahwa lokasi terkonfirmasi tetap muncul pada detail meeting.

**Pertanyaan UAS.** Bagaimana aplikasi membedakan akses lokasi meeting dari akses dokumen reservasi? Mengapa pengecekan bentrok diulang setelah baris ruang dikunci?
