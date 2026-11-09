# Pemulihan database admin — 25 Oktober 2026

## Hasil

Database aktif `kominfo` dipulihkan secara additive sesudah pengujian di database terpisah `kominfo_recovery_preview_20261025`. Akun, password hash, role, dan tabel yang sebelumnya tersedia dipertahankan. Tidak menggunakan migrate:fresh atau mengimpor SQL lama yang rusak.

Data yang tersedia setelah pemulihan:

| Data | Jumlah |
| --- | ---: |
| OPD | 32 |
| Definisi dataset | 178 |
| Definisi field | 978 |
| Nilai statistik tahun 2024 | 251 |
| Kabupaten + kecamatan | 16 |

Definisi berasal dari `database/seeders/data/_93dataset_bab*.json` dan `SangiheOpdDatasetsBlueprint.php`. Jumlah aktual blueprint sektoral adalah 90, ditambah 88 dataset operasional. Angka statistik diimpor dari `OfficialDistrictStatisticsSeeder` dan `StatistikSektoral2024PanganSeeder`, yang mencantumkan tabel sumber Buku Statistik Sektoral Kabupaten Kepulauan Sangihe 2024. PDF sumber tersedia di root project. Ini rekonstruksi dari sumber lokal, bukan pemulihan seluruh transaksi/data input pengguna yang pernah hilang.

Seeder dummy/demo tidak dijalankan. Dataset tanpa angka sumber dibiarkan kosong. Akun lain dari ProductionUserSeeder tidak dibuat dan password admin tidak direset.

## Perbaikan yang diperlukan agar pemulihan berhasil

- Menyatukan alias kode BKPSDMD/BKPSDM, DPUPR/PUPR, dan DISTAMAN/DISTAN pada importer blueprint.
- Melengkapi dua fungsi yang hilang dari seeder statistik untuk field dan nilai per kecamatan.
- Mengganti namespace, kolom badge, action, icon, dan nama route yang tidak cocok dengan Filament v2 pada resource data sektoral.
- Memetakan relasi polymorphic wilayah ke model Wilayah.
- Menambahkan kolom workflow dan editor yang digunakan kode tetapi belum tersedia dalam migrasi lama.
- Memperbaiki pembatasan akses dan pemuatan relasi pada pemilih dataset.

## Verifikasi

`scripts/check_recovered_admin.php` menguji dashboard, katalog OPD, master OPD, daftar nilai, daftar nilai terfilter, detail nilai, verifikasi, profil, dashboard eksekutif, peta, detail OPD, dan infografis. Form input OPD diuji dengan role yang disimulasikan dalam memori; admin_kominfo tetap ditolak membuat data langsung sesuai policy yang ada.

`scripts/check_dataset_workflow_preview.php` menjalankan create, update, submit, verify, publish dan memastikan 3 audit log tersedia. Seluruh penulisan uji dilakukan pada preview dan di-rollback; angka 251 tidak mencakup data uji.

Backup sebelum perubahan: `storage/backups/admin-before-recovery-20261025-072454.json`, mencakup DDL asli dan baris seluruh tabel yang tersedia saat itu. Backup mengandung akun sehingga tidak dimasukkan ke Git atau direktori publik.

## Pengoperasian

Buka `/admin` dan gunakan akun Kominfo yang telah diberikan. Tidak perlu mengubah `.env` ke database preview.

Command pemulihan memakai daftar migrasi terpilih yang diuji dalam urutan dependency, bukan menjalankan seluruh migrasi lama. Beberapa migrasi lama yang masih pending membuat atau menghapus tabel legacy dan belum direkonsiliasi; jangan menjalankan seluruh chain tersebut untuk mengulang pemulihan. `app:apply-admin-recovery --apply` juga akan menolak bila tabel dataset sudah tersedia, untuk melindungi data yang sudah diisi.
