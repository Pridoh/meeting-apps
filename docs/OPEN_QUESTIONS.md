# Keputusan yang menunggu pemilik proyek

| Kapan | Pertanyaan dan usulan |
|---|---|
| Saat serah terima Fase 1 | Gemini dan Fake tersedia sesuai §14. Tetapkan fase untuk GroqProvider (§3) dan cakupan halaman Profil (§6). |
| Sebelum Fase 2 | Saat undangan accepted dicabut, apakah keanggotaan juga dicabut? Tetapkan aturan mengundang kembali dan zona hari untuk kuota kirim ulang. |
| Sebelum desain undangan Fase 2 | Setujui penyimpanan riwayat efektif undangan, keanggotaan dan jadwal untuk snapshot terlambat; tentukan representasi tenggat per peserta bila satu meeting terlibat beberapa kelompok bentrok. |

Pertanyaan lanjutan menurut temuan audit:

- QA-03/04, saat serah terima Fase 1 — implementasi memakai satu anggaran 30 detik termasuk retry dan membatalkan review ketika isi draf berubah. Konfirmasi perilaku ini saat mencoba alur manual.
- QA-06/08, sebelum Fase 2–3 — bagaimana memperlakukan undangan kedaluwarsa pada rekap dan pilihan alternatif saat salah satu meeting sudah dimulai? Tetapkan contoh hasil untuk A–B–C dan peserta lama tanpa undangan.
- QA-09/10, baseline Fase 4 — draf dapat disimpan sebelum lengkap. Versi lampiran tetap privat. ORG-01–05 telah diputuskan di PRD §16, termasuk batas satu pengajuan aktif lintas organisasi.

Rincian risiko, contoh kasus batas dan prioritas pengujian ada di AUDIT.md. Catat jawaban yang diterima di DECISIONS.md.
