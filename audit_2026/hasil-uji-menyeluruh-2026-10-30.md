# Hasil Uji Menyeluruh SIPANDU — 30 Oktober 2026

## Lingkup dan keamanan data

Seluruh input contoh dibuat hanya pada database uji SQLite `:memory:` melalui PHPUnit `RefreshDatabase`. Tidak ada migrasi, seed, atau input yang dijalankan ke MySQL aplikasi, sehingga data OPD produksi/lokal tidak tercampur. Pengujian menggunakan definisi katalog yang ada di `SangiheOpdDatasetsBlueprint.php` dan file `_93dataset_bab*.json`.

## Hasil alur aktif

- **32 OPD, 178 dataset**: setiap dataset menerima satu input sintetis; siklus draft → kirim oleh OPD → verifikasi dan penerbitan oleh Kominfo → ekspor JSON berhasil. Termasuk cakupan wilayah kabupaten, kecamatan, dan desa serta field numerik, enum, referensi, dan geometri. **1 tes, 1.075 assertion, lulus.**
- **Mobile/kecamatan**: petugas membaca keluarga yang telah terbit untuk desanya, desa lain ditolak; titik koordinat dibuat dan ditautkan ke keluarga terpilih; kecamatan memeriksa dan meneruskan; Kominfo menyetujui. **1 tes, 13 assertion, lulus.**
- **Portal dan admin**: 16 endpoint publik/health diuji, halaman data dataset, halaman input, halaman verifikasi, statistik/export/template, serta unggah CSV. **1 tes, 24 assertion, lulus.**
- **Impor CSV dan XLSX**: parsing, validasi, baris invalid, dan nilai nol diuji. **3 tes, 10 assertion, lulus.**
- **Role/otorisasi**: enum role (10 tes/59 assertion), admin Kominfo (1/5), dashboard (1/1), upgrade Filament (4/12), user role field (1/2), dan autentikasi/redirect (2/5) lulus.
- **Migrasi uji SQLite** berhasil setelah indeks lama pada `dataset_values.periode_id` dihapus sebelum kolomnya, menghilangkan error migrasi SQLite.

## Perbaikan saat audit

- Impor XLSX kini mengubah sel `periode_label` numerik menjadi teks sebelum validasi. Sebelumnya impor XLSX gagal ketika spreadsheet menyimpan periode sebagai angka.
- Workflow batch kecamatan/mobile kini menulis riwayat ke `workflow_logs` ketika tabel workflow lama sudah tidak ada. Sebelumnya submit titik mobile menghasilkan HTTP 500 karena mencoba mengakses `workflow_instances` yang sudah dihapus.
- Pemeriksaan hak kelola pengguna Admin Kominfo diselaraskan dengan menu dan role yang dapat ditetapkan. Admin Kominfo kini dapat mengelola akun OPD/wilayah biasa; akses untuk akun admin pusat tetap dibatasi.
- Pendaftaran publik tidak lagi menawarkan role `admin_opd`; modul Disperindag dikembalikan ke kunci role `ekonomi` yang diharapkan.

## Pembersihan tes warisan

- Tes `DebugTest` dihapus karena hanya melakukan `dump()` respons endpoint lama lalu selalu lulus tanpa memeriksa perilaku.
- Tes NIK mobile lama diganti dengan tes endpoint titik koordinat aktif: pembuatan batch untuk kecamatan, retry idempoten, dan larangan mengirim titik ke desa di luar cakupan.
- Tes batch lama yang membuat data kesehatan/pendidikan dari tabel pensiun diganti dengan tes alur lapangan kecamatan → Kominfo, otorisasi lintas kecamatan, dan permintaan revisi/notifikasi.
- Tes notifikasi dan halaman Filament memakai `Wilayah` serta item `MobileFieldPoint`; tes role memakai daftar `AppRole` yang berlaku. UserScope memakai role Dukcapil yang ada di enum.
- Halaman detail batch kini memakai `workflow_logs` untuk riwayat, komentar, dan alasan revisi ketika tabel workflow lama sudah tidak tersedia; sebelumnya halaman review melempar SQL error meskipun endpoint mobile sudah berhasil menyimpan batch.
- Dengan PHP 8.5.12 dan ekstensi SQLite/OpenSSL lokal: tes endpoint mobile **3/3 lulus**, batch workflow **3/3 lulus**, notifikasi **2/2 lulus**, resource batch **2/2 lulus**, dan UserScope **10/10 lulus**. `FinalRoleTest` belum selesai dieksekusi; tes tersebut menjalankan seeder role/perizinan yang sangat lambat pada SQLite.
- Seluruh feature suite belum dinyatakan hijau karena belum semua berkas dijalankan ulang setelah migrasi tes ini.

## Batas audit

Pengujian input lintas seluruh OPD dijalankan secara sintetis dan terisolasi; saya sengaja tidak mengirim data contoh ke database MySQL aplikasi. Perlu dijalankan di server setelah memastikan backup/lingkungan staging tersedia bila yang dimaksud adalah uji penerimaan langsung terhadap data sebenarnya.
