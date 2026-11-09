# Pembersihan dan performa — 25 Oktober 2026

13 file hasil unduhan HTML, teks ekstraksi, dan cookies debug dipindahkan dari root project ke `storage/cleanup-archive/2026-10-25`. File dapat dipulihkan dengan memindahkannya kembali ke root. Dokumen PDF, backup, source, dependency, dan aset yang belum dipastikan pemakaiannya dipertahankan.

## Perbaikan

- Query daftar OPD dibatasi di SQL sebelum hasil dimuat, dan hanya kolom yang diperlukan diambil.
- Agregasi dashboard dibatasi ke dataset OPD yang terlihat.
- Dataset terbaru mengikuti scope OPD pengguna (sebelumnya mengambil seluruh OPD).
- Infografis memuat nilai dataset melalui eager loading, mengganti satu query tambahan untuk setiap dataset dengan satu query gabungan.
- Font admin memakai fallback lokal tanpa stylesheet Google Fonts yang menunda rendering pada jaringan lambat.
- Script `serve-dev.ps1` menjalankan artisan dari root yang benar, dengan `--no-reload` untuk menghindari restart watcher saat development.
- PHPUnit dipaksa memakai SQLite dalam memori. Guard dijalankan setelah bootstrap, sebelum trait pengujian dapat menjalankan migrasi.

## Kondisi database yang menghalangi validasi penuh

Audit baca-saja menemukan database aktif `kominfo` hanya memiliki 12 tabel; `users` kosong dan tabel dataset tidak tersedia. Ada tabel `workflow_instances` dari migrasi yang terhenti. Tabel yang tersisa tidak boleh dianggap sampah karena sebagian besar dibutuhkan autentikasi, permission, dan antrean Laravel.

Pengujian pada turn sebelumnya dijalankan dengan konfigurasi PHPUnit yang masih memakai database aplikasi. Hasil audit konsisten dengan RefreshDatabase yang berhenti pada migrasi workflow. Jangan menjalankan migrate:fresh atau seed sebagai pengganti pemulihan data.

Backup SQL tersedia: `backups/kominfo_full_backup_20261017_123423.sql` (17 Oktober 2026). Walaupun bernama full backup, file ini berisi perintah CREATE TABLE LIKE tabel yang sama dan bukan definisi skema mandiri; tabel dataset_values juga tidak tercantum. Jangan mengimpornya langsung sebagai backup lengkap. Backup JSON lama tersedia di `storage/backups/kominfo-before-dummy-cleanup.json`. Pemulihan perlu cadangan yang valid dan keputusan pengguna karena cadangan lama tidak mencakup perubahan setelah tanggal backup.

Waktu bootstrap CLI sebelum cache: sekitar 1605 ms. Angka ini bukan pengukuran reload browser. Kecepatan dashboard yang memakai data belum dapat dibandingkan sampai database dipulihkan.
