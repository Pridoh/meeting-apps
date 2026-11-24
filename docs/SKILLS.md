# Pemetaan skill proyek

Skill adalah panduan kerja agen, bukan fitur aplikasi atau pengganti PRD. Baca `SKILL.md` yang relevan saat mengerjakan tugas; instruksi pengguna dan aturan proyek tetap berlaku. Lokasi di bawah relatif terhadap akar repositori.

| Skill | Gunakan saat | Contoh di RAPATIN |
|---|---|---|
| [ponytail](../.codex/skills/ponytail/SKILL.md) | Membuat, memperbaiki, atau merapikan kode dengan solusi terkecil yang lengkap. | Validasi jadwal meeting dan tes batasnya tanpa menambah abstraksi yang belum dibutuhkan. |
| [ponytail-review](../.codex/skills/ponytail-review/SKILL.md) | Diminta meninjau perubahan tertentu; menghasilkan temuan, tidak mengubah kode. | Tinjau diff undangan, termasuk pemanggil, otorisasi, dan tes. |
| [ponytail-audit](../.codex/skills/ponytail-audit/SKILL.md) | Diminta mengaudit repositori atau cakupan yang ditentukan; lapor tanpa mengubah kode. | Telusuri alur akses, penyimpanan, antrean, dan risiko persaingan reservasi. |
| [anti-slop-design](../.codex/skills/anti-slop-design/SKILL.md) | Membangun antarmuka visual yang memiliki arah desain jelas. | Rancang dashboard yang sesuai pengguna dan konteks RAPATIN. |
| [design-taste-frontend](../.codex/skills/design-taste-frontend/SKILL.md) | Membuat landing page, portofolio, atau mendesain ulang halaman pemasaran; audit konteks sebelum perubahan. Dashboard dan formulir bertahap di luar cakupan skill ini. | Perbaiki landing page dengan mempertimbangkan tampilan dan pola yang sudah ada. |
| [micro-interactions](../.codex/skills/micro-interactions/SKILL.md) | Membuat atau mengevaluasi feedback, transisi, loading, dialog, atau interaksi lainnya. | Tentukan trigger, aturan, feedback, dan loop untuk status pengiriman undangan; dukung reduced motion. |
| [web-design-guidelines](../.codex/skills/web-design-guidelines/SKILL.md) | Diminta meninjau UI, UX, atau aksesibilitas. | Periksa file Blade yang ditentukan memakai pedoman terbaru dari sumber yang disebut dalam skill. |
| [caveman](../.codex/skills/caveman/SKILL.md) | Pengguna meminta jawaban singkat atau mode caveman. | Jelaskan hasil pemeriksaan secara ringkas tanpa menghilangkan fakta atau risiko. |

Untuk tugas yang mencakup beberapa aspek, gabungkan skill yang relevan: misalnya `ponytail` untuk perubahan kode dan `micro-interactions` untuk umpan balik tombol. Jangan menganggap panduan desain sebagai mandat mengganti identitas visual yang sudah disepakati. Gunakan [panduan belajar](LEARNING.md) dan PRD untuk memahami konteks produk terlebih dahulu.

## Pembagian peran agen proyek

Peran dan batas delegasi mengikuti `AGENTS.md` dan berkas di `.codex/agents/`. Skill digunakan sesuai tugas, bukan sebagai alasan menambah cakupan.

| Peran | Skill terkait | Tanggung jawab |
|---|---|---|
| Lead | `ponytail`, serta skill yang relevan dengan tugas | Memiliki kontrak, integrasi, keputusan, dan progres; membatasi delegasi dan memeriksa hasil sebelum serah terima. |
| [backend](../.codex/agents/backend.toml) | `ponytail` | Mengimplementasikan backend dan menulis serta menjalankan tes penerimaan dan regresi rutin. |
| [frontend](../.codex/agents/frontend.toml) | `anti-slop-design`, `micro-interactions`, `web-design-guidelines`; `design-taste-frontend` untuk landing page | Mengerjakan Blade, CSS, dan interaksi sesuai bagian 6 PRD; meninjau UI saat diminta. |
| [qa](../.codex/agents/qa.toml) | `ponytail-review`; `ponytail-audit` untuk audit repositori sesuai cakupan | Meninjau perubahan kompleks atau sensitif secara independen dan menguji celah yang ditemukan. |
| [learning](../.codex/agents/learning.toml) | `ponytail`, `ponytail-review` | Sekali per fase, menyederhanakan kode bila bermanfaat, menguji perilaku terdampak, dan memperbarui panduan belajar Indonesia. |

`caveman` mengatur gaya jawaban ketika diminta pengguna dan tidak terikat ke satu peran. Setiap fase tetap memerlukan seluruh Definition of Done dan percobaan manual manusia sebelum fase berikutnya.
