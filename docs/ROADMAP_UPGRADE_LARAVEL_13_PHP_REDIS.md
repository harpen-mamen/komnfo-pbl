# Roadmap Upgrade Laravel 13, PHP, Dependency, dan Redis

Tanggal audit: 25 Oktober 2026  
Status: audit selesai; roadmap disiapkan, belum ada dependency, konfigurasi runtime, atau database yang diubah.

## Ringkasan kondisi saat ini

| Area | Kondisi yang ditemukan | Dampak |
|---|---|---|
| Laravel / PHP | Laravel 10.50.2; CLI PHP 8.3.33 (Windows x64, ZTS). | Laravel 10 sudah melewati masa perbaikan bug dan keamanan. PHP 8.3 memenuhi minimum Laravel 13, tetapi bukan target PHP yang diminta. |
| Filament / Livewire | Filament 2.17.59 dan Livewire 2.12.8; terdapat 147 file di `app/Filament`. | Keduanya membatasi framework lama. Perpindahan ke Filament 5 mencakup tiga major version dan perubahan besar form, table, action, widget, panel, Livewire, dan asset. |
| Composer | Composer 2.8.12; `composer.lock` tersedia. Constraint project masih meminta `laravel/framework ^10.10`, `filament/filament ^2.17`, `sanctum ^3.3`, dan `maatwebsite/excel ^1.1`. | Composer mengonfirmasi paket terkunci yang tidak kompatibel dengan Laravel 13, termasuk Filament 2, Livewire 2, Sanctum 3, Collision 7, dan beberapa dependensi Filament. |
| Excel | `maatwebsite/excel` terkunci pada 1.1.5 (rilis 2014). Export menggunakan `Maatwebsite\Excel\Concerns\FromCollection`, yang tidak tersedia pada paket terpasang. Tombol import merujuk `App\Imports\DatasetValueImport`, tetapi class itu tidak ditemukan di source. | Import/export harus dipulihkan dan dimigrasikan sebagai pekerjaan tersendiri; jangan menganggap fitur ini sehat hanya karena Composer selesai. |
| Frontend / CSS admin | Tidak ada `package.json` atau `vite.config.js` di root. Build Next berada di `frontend/`: Next 14.2.15, React 18, Tailwind 3.4.13, Node 22.20.0. Tailwind admin dibangun lewat script `build:admin-css` di package frontend. | Filament 5 memerlukan Tailwind CSS 4.1+ untuk custom theme. Pisahkan/migrasikan build admin dengan hati-hati agar UI publik Next tidak ikut rusak. |
| Redis | Service Redis Windows terpasang dan berstatus berjalan di `127.0.0.1:6379`. `.env` saat ini memakai cache `file`, session `file`, queue `sync`, serta `REDIS_CLIENT=predis`. Paket `predis/predis` tidak ditemukan di lockfile dan ekstensi PHP `redis` belum terpasang. | Redis tersedia sebagai server tetapi belum siap dipakai aplikasi. Tetapkan satu client Redis yang benar-benar tersedia pada PHP lokal, web server, CI, dan produksi. |
| Pemakaian cache / queue | Sejumlah endpoint publik telah memakai `Cache::remember`; kode aplikasi tidak menunjukkan job `ShouldQueue` saat audit. | Redis cache berpotensi mempercepat respons berulang. Mengubah queue ke Redis belum memberi manfaat tanpa job dan worker yang berjalan. |
| Bootstrap / database | Struktur aplikasi masih memakai `app/Http/Kernel.php`, `app/Console/Kernel.php`, dan `app/Exceptions/Handler.php`. Ada 46 migration aktif dan 53 file di `_archive_legacy`. | Pertahankan struktur Laravel 10 selama upgrade framework. Jalankan migrasi hanya pada salinan database/preview dan tinjau migration satu per satu. |

Laravel 13 mendukung PHP 8.3 sampai 8.5 dan mensyaratkan minimal PHP 8.3. Target yang disarankan untuk proyek ini adalah PHP 8.5 dengan patch terbaru yang tersedia saat pelaksanaan. Filament 5 mensyaratkan Laravel 11.28+, PHP 8.2+, dan Tailwind 4.1+.

## Target versi

- Laravel `^13.0`.
- PHP `8.5.x` pada CLI, server web, CI, dan mesin worker.
- Filament `^5.0`, Livewire yang disyaratkan Filament 5, dan Tailwind CSS `^4.1` untuk theme admin.
- Naikkan dependency Composer langsung dan transitif satu per satu ke major yang secara resmi mendukung Laravel 13. Prioritas audit: Tinker 3, Sanctum 4, Spatie Permission 7/8, PHPUnit 12, Collision/Ignition terbaru yang kompatibel, dan Maatwebsite Excel 4.
- Naikkan dependency frontend dalam jalur terpisah: migrasi Tailwind admin terlebih dahulu; kemudian Next/React dan dependency publik berdasarkan versi Node LTS dan kompatibilitas yang didukung pada saat eksekusi.
- Redis: gunakan satu client konsisten di lokal, CI, dan produksi. Pilihan pertama adalah PhpRedis bila ekstensi PHP 8.5 yang cocok tersedia di seluruh target; jika tidak, pasang `predis/predis` secara eksplisit. Jangan membiarkan konfigurasi memilih `predis` tanpa package terpasang.

Pemeriksaan Composer terhadap lockfile menunjukkan blocker yang nyata, bukan sekadar constraint root: Filament/Livewire/Sanctum versi sekarang hanya mengizinkan Illuminate lama; `nunomaduro/collision` 7 konflik dengan Laravel 11+; `akaunting/laravel-money` 4 dan `ryangjchandler/blade-capture-directive` 0.3 membatasi Illuminate sampai 10; `danharrin/livewire-rate-limiting` 1.3 membatasi sampai 11; dan Tinker 2.11 membatasi sampai Laravel 12. Sebagian paket tersebut adalah dependency Filament 2 dan dapat ikut terganti saat panel dimigrasikan. Jangan hapusnya satu per satu sebelum solver dan source usage diperiksa.

## Urutan eksekusi dan gerbang kelulusan

### 0. Siapkan jalur aman dan baseline

1. Catat commit, status working tree, versi PHP web/CLI, Composer, Node, MySQL, ekstensi PHP, dan konfigurasi worker pada setiap lingkungan.
2. Buat branch/worktree upgrade terpisah. Simpan backup database dan asset; pertahankan salinan preview untuk percobaan.
3. Rekam baseline: halaman admin, login dan role, API mobile, input/edit/submit/verify/publish, peta/infografis, import/export, jumlah dataset/nilai, serta waktu respons p50/p95 untuk rute yang lambat.
4. Jangan menjalankan `migrate:fresh`, seeder data contoh, atau perubahan database langsung pada database aktif.

**Gerbang:** versi dan jumlah data tercatat; ada cara rollback ke kode dan database sebelum perubahan.

### 1. Siapkan runtime PHP dan toolchain

1. Siapkan PHP 8.5 patch terbaru berdampingan dengan PHP 8.3 sampai smoke check selesai. Samakan PHP CLI dan PHP web server; jangan hanya mengubah `config.platform` Composer.
2. Aktifkan dan verifikasi ekstensi yang diperlukan aplikasi: PDO MySQL, mbstring, XML, cURL, intl, zip, GD, dan Redis jika memilih PhpRedis.
3. Naikkan Composer ke rilis stabil yang didukung dan gunakan Node LTS yang kompatibel dengan Next target.
4. Pastikan Redis dapat dijangkau dari runtime PHP yang dipakai web server, bukan hanya dari `redis-cli`.

**Gerbang:** `php -v`, `php -m`, `composer check-platform-reqs`, akses MySQL, akses Redis, dan build frontend berhasil dari runtime target.

### 2. Pulihkan dan pisahkan dependency legacy

1. Migrasikan Laravel Excel 1.1 ke API modern pada branch tersendiri. Cocokkan importer, export class, format template, CSV/XLSX, validasi, rollback saat satu baris gagal, dan laporan error.
2. Pulihkan/putuskan tombol import dataset yang saat ini menunjuk class yang tidak ada.
3. Audit dependency transitif Filament 2 yang membatasi Laravel; hapus hanya jika sudah dipastikan tidak dipakai atau sudah digantikan oleh Filament baru.
4. Jalankan Composer solver dry-run untuk kombinasi target pada branch, lalu tinjau konflik dan perubahan lockfile sebelum instalasi.

**Gerbang:** Excel import/export lulus uji pada data salinan; Composer dapat menyelesaikan versi per tahap tanpa menghapus package yang masih digunakan.

### 3. Upgrade framework dan admin panel bertahap

Jangan melompat sekaligus dari Laravel 10 / Filament 2 ke Laravel 13 / Filament 5.

1. Laravel 10 → 11; pindahkan Filament 2 → 3 dan Livewire 2 → 3. Pertahankan struktur bootstrap lama saat tahap ini.
2. Laravel 11 → 12; pindahkan Filament 3 → 4. Migrasikan struktur schema/form/action dan theme sesuai panduan Filament.
3. Laravel 12 → 13; pindahkan Filament 4 → 5 dan Livewire ke major yang disyaratkan. Terapkan panduan resmi Laravel 13 untuk cache/session, kontrak, dan perubahan API.
4. Perbarui Sanctum, Permission, Tinker, PHPUnit, Collision, Ignition, DomPDF, Guzzle, dan package lain mengikuti batas kompatibilitas setiap checkpoint.
5. Tinjau 147 file Filament dan Blade override vendor. Pertahankan perubahan UI lokal yang diperlukan; jangan menimpa folder custom view secara massal.

**Gerbang tiap major:** bootstrap, route, login, role/permission, halaman admin, API, dan alur data berjalan sebelum lanjut ke major berikutnya.

### 4. Migrasikan CSS dan asset admin

1. Migrasikan theme admin ke Tailwind 4.1+ dan directive/source scanning yang sesuai Filament 5.
2. Pisahkan output CSS admin dari styling Next publik agar pembaruan admin tidak mengubah tampilan portal publik tanpa sengaja.
3. Build CSS admin dan Next dari lockfile bersih; periksa halaman list, form, widget, modal, dropdown, dark mode, dan responsif mobile.
4. Baru setelah dua build stabil, naikkan Next/React serta package frontend lain secara bertahap, dengan lockfile dan smoke check tersendiri.

**Gerbang:** tidak ada utility class Filament yang hilang; kedua build frontend selesai dan halaman admin/publik lolos pemeriksaan visual.

### 5. Aktifkan Redis secara terukur

1. Pasang Redis client yang dipilih dan tetapkan koneksi/prefix serta DB Redis terpisah untuk cache, session, dan queue bila queue nanti diaktifkan.
2. Perbarui konfigurasi cache Laravel 13 (`CACHE_STORE`) dan contoh environment. Mulai dari Redis untuk cache; uji key TTL, invalidasi, lock, dan fallback saat Redis tidak tersedia.
3. Pindahkan session ke Redis setelah strategi logout/re-auth disepakati; perpindahan dari file session dapat membuat pengguna perlu login kembali.
4. Biarkan `QUEUE_CONNECTION=sync` sampai aplikasi benar-benar punya job asinkron dan worker terkelola. Setelah itu, rencanakan worker/service monitor dan uji retry, kegagalan, serta shutdown.
5. Ukur rute sebelum/sesudah cache hangat. Tambahkan cache hanya untuk query mahal yang bisa diberi TTL dan invalidasi yang benar; jangan meng-cache hasil verifikasi/publikasi tanpa invalidasi event yang jelas.

**Gerbang:** Redis hit/miss dan invalidasi terukur; halaman utama lebih cepat tanpa menampilkan data lama setelah perubahan status; aplikasi tetap memberi error terkontrol jika Redis berhenti.

### 6. Validasi data, deployment, dan rollback

1. Jalankan seluruh unit/feature/integration test pada PHP target dan DB preview; tambahkan smoke untuk kontrak mobile, setiap role admin/OPD/Walidata, GIS, import/export, dan halaman publik.
2. Jalankan migration pending hanya pada salinan database, inspeksi schema diff, lalu bandingkan jumlah dataset/nilai dan relasi penting dengan baseline.
3. Uji deploy di staging: install dari `composer.lock` dan `package-lock.json`, build assets, cache config/routes/views, worker, health check, dan satu siklus backup/restore.
4. Rilis dengan backup dan prosedur rollback kode/config/database. Pantau error log, p95, Redis memory/latency, query MySQL, dan kegagalan worker sebelum menghapus PHP lama.

**Gerbang akhir:** tidak ada kehilangan data; seluruh alur penting berjalan; hasil benchmark menunjukkan perubahan nyata; prosedur rollback sudah diuji.

## Risiko utama

- Filament 2 ke 5 adalah bagian dengan perubahan kode terbanyak; ratusan pemakaian API tersebar pada resource, page, widget, dan Blade.
- Dependency Excel lama tidak sesuai dengan API yang dipakai source dan import class tidak ditemukan.
- Redis server aktif tetapi PHP tidak memiliki ekstensi Redis dan package Predis belum ada di lockfile walaupun `.env` memilih `predis`.
- Menaikkan Laravel, Filament, Tailwind, Next, dan React dalam satu perubahan akan membuat penyebab regresi sulit dilacak. Jalankan per checkpoint dengan lockfile dan build terpisah.
- Redis sendiri tidak otomatis mempercepat query pertama. Keuntungan bergantung pada beban cache, waktu query, TTL, dan invalidasi data.
- File migration lama tidak boleh dites dengan reset database. Gunakan preview clone dan migrasi additive yang sudah ditinjau.

## Sumber versi resmi

- [Laravel 13 release notes dan support policy](https://laravel.com/docs/13.x/releases)
- [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade)
- [Laravel 11 upgrade guide untuk aplikasi Laravel 10](https://laravel.com/docs/11.x/upgrade)
- [Filament 5 installation dan requirement](https://filamentphp.com/docs/5.x/introduction/installation)
- [PHP supported versions](https://www.php.net/supported-versions.php)
- [Laravel Excel supported versions](https://github.com/SpartnerNL/laravel-excel)
- [Spatie Permission compatibility](https://github.com/spatie/laravel-permission/blob/main/docs/prerequisites.md)
