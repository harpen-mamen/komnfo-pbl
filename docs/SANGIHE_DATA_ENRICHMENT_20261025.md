# Pengayaan data dan penyederhanaan admin — 25 Oktober 2026

## Sumber dan cakupan

Sumber statistik: [portal resmi Pemkab Sangihe, tahun data 2024](https://sangihekab.go.id/statistik-sektoral-tahun-data-2024/). Publikasi ini memuat data tahun 2024, bukan data kondisi terkini 2026. Ekstraksi lokal yang diperiksa berada di `storage/cleanup-archive/2026-10-25/stat_sek.txt`; PDF tersedia di workspace. Nomor halaman mengikuti halaman tercetak publikasi.

Fixture yang ditinjau: `database/seeders/data/SangiheSectoral2024Supplement.php`.

Tambahan: 220 baris statistik dari 29 tabel berikut:

| Kelompok | Kode tabel |
| --- | --- |
| Pendidikan, kesehatan, gender, desa | 3.16, 3.17, 3.20, 3.21, 3.29, 3.30, 3.32 |
| Pertanian dan perikanan | 4.41, 4.42, 4.43, 4.44, 4.46, 4.47, 4.48 |
| Infrastruktur dan perhubungan | 5.49, 5.50, 5.54, 5.55, 5.57, 5.58 |
| Ketenteraman dan lingkungan | 8.82, 8.83, 8.84, 8.85, 8.86, 8.87 |
| Pemerintahan digital | 9.88, 9.90, 9.92 |

Ini pengayaan tabel yang dapat dipetakan secara jelas, bukan klaim seluruh buku telah diimpor. Data agregat tidak diubah menjadi daftar aset/penduduk individual. Data pribadi, jumlah aset per lokasi, kondisi jalan/jembatan, tahun pembangunan, serta koordinat yang tidak disediakan tidak dibuat-buat.

## Lokasi publik

Fixture: `database/seeders/data/SangiheInfrastructureSources.php`.

| Lokasi | Latitude | Longitude | Sumber |
| --- | --- | --- | --- |
| Bandara Naha, titik ARP | 3.685555556 | 125.529166667 | https://hubud.kemenhub.go.id/bandara/31 |
| Pelabuhan Tahuna | 3.60119629 | 125.50158691 | https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/SARANA_PRASARANA/MapServer/1 — objectid 501 |
| Puskesmas Kaluwatu | 3.4386328 | 125.5823597 | https://santertb.sangihekab.go.id/faskes/1 |
| RSUD Liun Kendage | 3.6082851 | 125.4782947 | https://santertb.sangihekab.go.id/faskes/2 |

Dua dataset master baru memisahkan rujukan lokasi transportasi dan fasilitas kesehatan dari statistik tahun 2024. Tahun 2026 berarti tahun akses, bukan tahun survei/pembangunan. Titik merupakan koordinat yang dilaporkan sumber, bukan verifikasi batas aset atau petunjuk navigasi.

Direktori SANTER TB memberi koordinat di luar Sangihe pada 17 fasilitas lainnya (lintang negatif). Identitas dan URL sumber disimpan, tetapi latitude, longitude, dan geometri tidak diimpor. Tidak ada pembalikan tanda lintang atau titik fallback. Koordinat jalan/jembatan belum ditambahkan karena belum ditemukan sumber primer yang cukup jelas untuk tiap objek.

Snapshot penelitian: `storage/app/source-imports/2026-10-25/`. Pengambilan ulang menggunakan `scripts/fetch_sangihe_public_sources.ps1`, dengan pemeriksaan sertifikat tetap aktif.

## Penanganan perbedaan sumber

- Nilai kosong tetap null, bukan nol; tidak dibuat rincian bulan dari angka tahunan.
- Angka harapan hidup mengikuti tabel 3.21; perbedaan tahun/nilai dalam narasi dicatat.
- Pelanggan air belum terlayani mengikuti angka tabel, dengan perbedaan narasi dicatat.
- Harga ikan Biji Nangka bertuliskan ambigu `30.0000,00`: harga disimpan null, bukan ditebak; nilai produksi mengikuti tabel.
- Kelompok ukuran kapal mengikuti kelompok asli sumber, tidak dipecah tanpa data.
- Subtotal tabel produksi ikan tidak diimpor ulang sebagai jenis ikan agar tidak menjumlahkan dua kali.
- Grafik memisahkan satuan dan dimensi majemuk, mengecualikan tahun/koordinat dari metrik, serta tidak menggambarkan angka kosong sebagai nol.

## Penyederhanaan halaman

- Ringkasan menjadi dashboard utama; Input Data OPD dan daftar OPD ganda disatukan ke Katalog Dataset.
- Monitoring Kabupaten diarahkan ke dashboard eksekutif; URL lama tetap mengalihkan dengan pembatasan akses.
- Halaman impor penduduk serta resource penduduk/keluarga lama dinonaktifkan dari discovery, karena tabel/model operasionalnya telah diarsipkan sebelumnya. URL indeks diarahkan ke katalog, bukan dibuatkan tabel palsu.
- Hub laporan lama (berisi unduhan placeholder/modul yang sudah tidak tersedia) diarahkan ke katalog. Ekspor aktual ada pada dataset masing-masing. Kode ekspor lama tidak dihapus.
- Kiriman Lapangan dibedakan dari Verifikasi Data Sektoral; keduanya memang alur berbeda.
- Tiga kelas halaman duplikat dan dua view yang tidak lagi dirujuk dihapus setelah disalin ke `storage/cleanup-archive/2026-10-25/duplicate-pages/`; dapat dipulihkan dari arsip tersebut. File lama lain yang masih punya dependensi dipertahankan.

## Penerapan dan keamanan

Perintah `php artisan app:enrich-sangihe-data` selalu default ke database preview dan melakukan rollback. `--apply` diperlukan untuk menyimpan; `--database=mysql --apply` secara eksplisit menargetkan database utama `kominfo`.

Impor membuat snapshot dataset/field/value sebelum commit di `storage/backups/enrichment-*.json`. Marker sumber memastikan impor ulang tidak menggandakan atau menimpa baris lain, termasuk yang sudah di-soft-delete. Snapshot ini bukan backup penuh semua tabel.

Migrasi tambahan hanya `2026_10_25_000004_complete_admin_support_tables.php`: menambahkan tabel audit dan permohonan akun yang hilang tanpa menghapus tabel/data lain. Jangan menjalankan `migrate:fresh`, seeder massal, atau seluruh migrasi historis yang masih memiliki konflik.

## Verifikasi yang dapat dijalankan ulang

```
php scripts/check_sangihe_enrichment.php
php scripts/audit_dataset_pages.php --offset=0 --limit=180
php scripts/audit_admin_pages.php
php scripts/check_dataset_workflow_preview.php
```

Pemeriksaan sumber mengharapkan 180 dataset, 492 nilai (251 sebelumnya + 241 tambahan), 220 baris statistik cocok, empat titik peta, dan 17 fasilitas tanpa geometri. Pengujian workflow dilakukan dalam transaksi preview lalu dibatalkan.

`check_sangihe_enrichment.php --active`, `audit_admin_pages.php --active`, dan `check_recovered_admin.php --active` melakukan pemeriksaan baca saja pada database utama. Audit HTTP memeriksa render server, akses/redirect, daftar, formulir, dan infografis; bukan pengganti uji browser interaktif terhadap dropdown, unggahan, Leaflet, atau semua proses bisnis.

Build tema: `npm.cmd run build:admin-css` dari `frontend`. Peringatan Browserslist usang tidak menggagalkan build.

## Hasil penerapan

Sudah diterapkan pada database utama `kominfo`. Snapshot pra-impor: `storage/backups/enrichment-kominfo-20261025-082032.json`.

- Pencocokan aktif: 180 dataset, 492 nilai, 220 baris statistik sesuai fixture, empat titik peta, 17 fasilitas tanpa koordinat; tidak ada kegagalan.
- Impor ulang dry-run aktif: nol baris baru, 241 baris dikenali; rollback selesai.
- Preview: 540 respons dataset (180 dataset x daftar/formulir/infografis), nol kegagalan.
- Audit admin preview dan aktif: tidak ada HTTP 500; akses yang dilarang tetap 403, login/halaman lama redirect. URL formulir dataset tanpa parameter dataset memang 404; formulir dengan parameter diuji terpisah.
- Alur create/update/submit/verify/publish preview lulus, tiga log workflow; data pengujian di-rollback.
- Halaman inti aktif dan formulir own-OPD lulus; build Tailwind serta cache rute/view berhasil.
- Waktu render CLI tidak seragam (sebagian request awal sekitar 18 detik saat audit paralel, request lain ratusan milidetik). Belum dilakukan benchmark browser dan tidak diklaim semua reload sudah cepat.
