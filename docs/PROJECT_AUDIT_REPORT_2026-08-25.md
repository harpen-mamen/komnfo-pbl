# Laporan Audit Kondisi Proyek SIPANDU

Tanggal audit: 25 Agustus 2026  
Ruang lingkup: source code Laravel/Filament, API mobile, aplikasi Flutter, konfigurasi, migrasi, seeder, test, build frontend, dan status Git.  
Metode: inspeksi read-only, pencarian pola keamanan, lint/syntax check, route check, test suite, Pint, dan Vite build.

## Ringkasan eksekutif

Proyek sudah memiliki fondasi fitur yang luas: Laravel 10.50.2, Filament 2.17.59, RBAC Spatie, Sanctum, API mobile offline-sync, workflow submission, dashboard publik, dan modul AI. Sintaks PHP, route registration, dan build frontend berhasil.

Namun, proyek belum layak dianggap siap produksi. Risiko terbesar saat ini adalah:

1. **Kritis — secret aktif tersimpan di repository:** `.blackboxcli/settings.json` tracked Git dan memuat bearer token. Token harus segera dicabut/rotasi.
2. **Tinggi — data dan akses:** mode debug aktif, seeder membuat akun dengan password default `password`, backup JSON berisi PII dan hash password, dan resource mobile mengirim NIK/nomor HP.
3. **Tinggi — isolasi otorisasi:** cabang OPD pada `UserScope` dieksekusi sebelum cabang Puskesmas/Sekolah, sehingga scope fasilitas khusus tidak pernah tercapai untuk role tersebut.
4. **Tinggi — integritas sinkronisasi:** mobile membuat entitas sebelum batch; bila pembuatan batch gagal, entitas dapat tertinggal tanpa rollback.
5. **Tinggi — kebocoran lintas user:** cache konteks AI hanya memakai hash pertanyaan, padahal data dihitung memakai scope user aktif.
6. **Sedang — kualitas delivery:** 52 dari 67 test gagal pada run audit; mayoritas karena test mencoba MySQL yang tidak tersedia, ditambah satu bug role yang nyata. Pint menemukan 191 file dirty yang tidak memenuhi style.

Penilaian keseluruhan: **Cukup untuk pengembangan aktif, belum siap production deployment**.

## Kondisi teknis saat ini

| Area | Hasil |
|---|---|
| Runtime | PHP 8.2.12; Laravel 10.50.2; Filament 2.17.59 |
| Database target | MySQL (`DB_CONNECTION=mysql`) |
| Modul | Admin multi-level, OPD, kesehatan, pendidikan, peta publik, laporan, API mobile, AI |
| PHP syntax | Semua file `app`, `routes`, `config`, `database`, dan `tests` lulus `php -l` |
| Route registration | Berhasil; 156 route terdaftar |
| Frontend build | Berhasil dengan `npm.cmd run build` (Vite 5.4.21) |
| Test suite | 15 passed, 52 failed (67 test) pada `php artisan test --without-tty`; 52 kegagalan didominasi koneksi MySQL ditolak |
| Code style | Gagal; Pint melaporkan 191 file dirty tidak terformat |
| Dependency audit | Tidak dapat diverifikasi karena akses ke Packagist diblokir pada environment audit |
| Repository | Worktree sangat dirty: banyak file modified/untracked/deleted; baseline commit tidak merepresentasikan seluruh aplikasi yang sedang dijalankan |

## Temuan prioritas

### SEC-01 — Bearer token tracked di repository (Kritis)

File [`.blackboxcli/settings.json`](C:/pbl/.blackboxcli/settings.json:6) memuat header `Authorization: Bearer ...` dan file tersebut terdeteksi sebagai tracked Git. Nilai token tidak disalin ke laporan ini.

Dampak: siapa pun yang dapat membaca repository/riwayat Git berpotensi memakai akses remote-code. Menghapus baris pada working tree saja tidak menghapusnya dari history.

Tindakan: cabut/rotasi token sekarang, hapus secret dari seluruh history Git, tambahkan file ke ignore/secret manager, dan jalankan secret scan pada seluruh history.

### SEC-02 — Konfigurasi default tidak aman untuk deployment (Tinggi)

[` .env.example`](C:/pbl/.env.example:2) menetapkan `APP_ENV=local`, [` .env.example`](C:/pbl/.env.example:5) menetapkan `APP_DEBUG=true`, dan URL masih `http://localhost`. Environment aktif juga menunjukkan `APP_ENV=local` dan `APP_DEBUG=true`.

Dampak: exception dapat memaparkan stack trace, path, konfigurasi, dan detail internal saat salah konfigurasi deployment.

Tindakan: production harus memakai `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS, secret terpisah, `php artisan config:cache`, dan pemeriksaan startup yang menolak debug aktif di production.

### SEC-03 — Seeder production membuat akun dan password default (Tinggi)

[`DatabaseSeeder.php`](C:/pbl/database/seeders/DatabaseSeeder.php:79) dan [`ProductionUserSeeder.php`](C:/pbl/database/seeders/ProductionUserSeeder.php:56) membuat akun `*.sipdt.test` dengan `Hash::make('password')`. `DatabaseSeeder` juga memanggil `ProductionUserSeeder`.

Dampak: seeding pada database nyata dapat membuat akun yang mudah ditebak dan akun uji yang tidak seharusnya hidup di production.

Tindakan: pisahkan seeder demo/test dari production, jangan pernah memakai password statis, buat bootstrap admin satu kali melalui secret/CLI interaktif, dan wajibkan reset password serta MFA untuk admin.

### SEC-04 — Backup lokal mengandung data sensitif dan belum tercakup ignore (Tinggi)

[`storage/backups/kominfo-before-dummy-cleanup.json`](C:/pbl/storage/backups/kominfo-before-dummy-cleanup.json) berukuran sekitar 3.1 MB dan berisi data penduduk, email akun, serta hash password. File ini belum tracked saat audit, tetapi pola `storage/backups` tidak dilindungi secara eksplisit oleh [`.gitignore`](C:/pbl/.gitignore:1).

Dampak: backup dapat ikut ter-commit, tersalin ke artifact, atau terekspos tanpa kontrol retensi/enkripsi.

Tindakan: pindahkan backup ke storage terenkripsi di luar repository, tambahkan `storage/backups/` ke ignore, hapus data sensitif dari backup lokal yang tidak diperlukan, dan dokumentasikan retensi/restore test.

### AUTH-01 — Scope Puskesmas/Sekolah tertutup oleh cabang OPD (Tinggi)

[`AppRole.php`](C:/pbl/app/Enums/AppRole.php:158) memetakan role Puskesmas ke modul `kesehatan` dan role Sekolah ke `pendidikan`, sehingga `isOpdLevel()` bernilai true. Dalam [`UserScope.php`](C:/pbl/app/Support/UserScope.php:245), cabang `isOpdLevel()` mengembalikan filter `created_by` sebelum cabang Puskesmas [`UserScope.php`](C:/pbl/app/Support/UserScope.php:254) dan Sekolah [`UserScope.php`](C:/pbl/app/Support/UserScope.php:272) dapat dijalankan.

Dampak: pengguna Puskesmas/Sekolah berpotensi hanya melihat data yang dibuat sendiri atau tidak melihat data fasilitas yang seharusnya menjadi cakupannya. Ini memengaruhi confidentiality dan correctness dashboard/laporan.

Tindakan: bedakan kategori role OPD umum dari role fasilitas, atau evaluasi `isPuskesLevel()`/`isSekolahLevel()` lebih dahulu; tambahkan test scope lintas fasilitas dan role.

### AUTH-02 — Cache konteks AI tidak memasukkan identitas/scope user (Tinggi)

[`SipanduAIContext.php`](C:/pbl/app/Services/AI/SipanduAIContext.php:35) memakai key `ai:context:{sha1(question)}`. Namun nilai dihitung lewat query `visibleToCurrentUser()` di [`SipanduAITools.php`](C:/pbl/app/Services/AI/SipanduAITools.php:12).

Dampak: pertanyaan sama dari user berbeda dapat menerima agregat yang dicache dari user sebelumnya, termasuk lintas desa/kecamatan. Ini adalah potensi kebocoran data agregat.

Tindakan: masukkan user ID, role, dan scope (desa/kecamatan/OPD) ke cache key; lebih aman cache per request/user dan tambahkan test bahwa dua user dengan scope berbeda tidak berbagi hasil.

### DATA-01 — Mobile sync tidak atomic dan dapat meninggalkan data yatim (Tinggi)

[`MobileSyncController.php`](C:/pbl/app/Http/Controllers/Api/MobileSyncController.php:194) membuat Rumah/Keluarga/Penduduk sebelum memanggil `SubmissionBatchService` pada baris 100. Jika batch gagal, handler hanya mengubah status operasi menjadi failed pada baris 128–152; tidak ada transaksi yang membungkus pembuatan entitas dan batch.

Dampak: retry atau kegagalan workflow dapat meninggalkan entitas tanpa batch, duplikasi, atau state yang tidak konsisten dengan read model approved.

Tindakan: gunakan satu `DB::transaction()` untuk seluruh push, atau desain staging table lalu promote atomically; pastikan rollback mencakup entity, operation, item, dan batch.

### DATA-02 — Error internal dikembalikan ke client mobile (Sedang–Tinggi)

Pada [`MobileSyncController.php`](C:/pbl/app/Http/Controllers/Api/MobileSyncController.php:128), exception message mentah disimpan dan dikembalikan melalui `buildResponse` pada baris 152. Message dapat berisi SQL/table/path/provider detail.

Tindakan: log detail hanya di server dengan correlation ID; kirim pesan generik dan validation errors yang sudah disanitasi ke client.

### DATA-03 — Resource mobile mengekspos PII tingkat tinggi (Tinggi, tergantung kebijakan publikasi)

[`PendudukResource.php`](C:/pbl/app/Http/Resources/PendudukResource.php:20) mengirim NIK, nama, tanggal lahir, agama, pekerjaan, penghasilan, dan nomor HP. Endpoint mobile memang terautentikasi, tetapi response tersebut perlu dibatasi sesuai prinsip least privilege dan kebutuhan layar.

Tindakan: gunakan DTO/resource berbeda untuk list vs detail, mask NIK/nomor HP bila tidak diperlukan, audit logging akses, dan tetapkan retensi/cache policy di aplikasi mobile.

### API-01 — Parameter `per_page` tidak dibatasi (Sedang)

Empat endpoint mobile meng-cast input langsung lalu memanggil `paginate($perPage)` tanpa `min/max` pada [`MobileDataController.php`](C:/pbl/app/Http/Controllers/Api/MobileDataController.php:70), :125, :180, dan :237.

Dampak: client terautentikasi dapat meminta page size sangat besar dan membebani query/memori.

Tindakan: validasi `integer|min:1|max:100` dan gunakan default server-side.

### API-02 — Kontrak statistik publik memiliki bug field dan age 65+ (Sedang)

Response `/api/v1/stats` menulis key `kecamatan` dua kali pada [`PublicStatsController.php`](C:/pbl/app/Http/Controllers/Api/PublicStatsController.php:87) dan :101; nilai total kecamatan tertimpa summary array.

Selain itu, helper age distribution melakukan `explode('-', '65+')` pada [`PublicStatsController.php`](C:/pbl/app/Http/Controllers/Api/PublicStatsController.php:297) dan :334. Label `65+` tidak menghasilkan pasangan min/max yang valid, sehingga bucket usia 65+ salah/berpotensi selalu nol.

Tindakan: gunakan key terpisah (`total_kecamatan`, `kecamatan_summary`) dan parser bucket eksplisit dengan test boundary tanggal lahir.

### API-03 — CORS wildcard pada API (Sedang)

[`config/cors.php`](C:/pbl/config/cors.php:20)–:26 mengizinkan semua method, origin, dan header. `supports_credentials` memang false, tetapi wildcard tetap memperluas origin yang dapat membaca API publik/endpoint yang keliru terpublikasi.

Tindakan: batasi origin ke domain portal dan aplikasi yang disetujui; bedakan policy API publik dan API mobile.

### API-04 — Endpoint pengujian AI masih terdaftar (Sedang)

[`routes/web.php`](C:/pbl/routes/web.php:118) mendaftarkan `/test-ai-config` dan `/test-ai` untuk semua user terautentikasi. Endpoint kedua memanggil gateway AI secara nyata dan mengembalikan raw response.

Dampak: pemakaian quota/biaya tidak terkontrol, informasi konfigurasi terungkap, dan endpoint debug dapat terlupa saat deployment.

Tindakan: hapus dari production route, pindahkan ke command/test, atau lindungi dengan feature flag environment dan role super-admin plus throttle.

### QUALITY-01 — Test environment tidak mandiri (Tinggi untuk delivery)

[`phpunit.xml`](C:/pbl/phpunit.xml:21) hanya menetapkan `APP_ENV=testing`; konfigurasi SQLite in-memory pada baris 25–26 masih dikomentari. Akibatnya test memakai konfigurasi MySQL lokal. Run audit menghasilkan 52 kegagalan karena `SQLSTATE[HY000] [2002]` (MySQL tidak menerima koneksi).

Tindakan: aktifkan `DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` untuk test, pastikan migration/seed berjalan, dan sediakan profile integration test MySQL terpisah di CI.

### QUALITY-02 — Bug role terdeteksi test (Sedang)

`AppRoleEnumTest::test_opd_module_returns_correct_module` gagal: expected `pendidikan`, actual `ekonomi`, karena [`AppRole.php`](C:/pbl/app/Enums/AppRole.php:177) memasukkan `ADMIN_DISPEND`/`OPERATOR_DISPEND` ke mapping ekonomi. Keputusan domain perlu diklarifikasi lalu enum/test diselaraskan.

### QUALITY-03 — Code style dan perubahan tidak terkendali (Sedang)

Pint `--test --format=json --dirty` melaporkan 191 file yang tidak memenuhi style. Git status juga menunjukkan banyak file modified/untracked/deleted, termasuk README/AGENTS yang terhapus dan beberapa file sementara. Ini meningkatkan risiko review, merge, dan rollback.

Tindakan: pecah perubahan menjadi commit tematik, pulihkan/hapus file hanya setelah verifikasi pemilik, jalankan formatter pada branch khusus, dan pasang CI untuk syntax, Pint, test, build, dependency/secret scan.

### OPS-01 — Queue/database dan observability belum production-ready (Sedang)

Environment aktif menggunakan `QUEUE_CONNECTION=database`, tetapi reliabilitas worker, retry, failed job alerting, dan backup queue belum tervalidasi. `APP_DEBUG=true` serta `LOG_LEVEL=warning` juga membuat diagnosis dan keamanan tidak seimbang.

Tindakan: siapkan worker supervisor/container, retry/backoff dan failed-job alert, structured logging tanpa PII, health check DB/queue/cache, serta runbook restore.

### OPS-02 — Migrasi memakai banyak timestamp prefix yang sama (Rendah–Sedang)

Terdapat beberapa grup nama migrasi dengan prefix timestamp identik, termasuk dua file `2026_08_02_000001` dan dua file `2026_08_07_000001`. Laravel masih mengurutkan filename, tetapi pola ini rapuh ketika branch digabung dan migration dependency berubah.

Tindakan: gunakan timestamp unik monotonik untuk migrasi baru dan verifikasi `migrate:fresh` pada database kosong.

## Hal yang sudah baik

- Validasi input cukup luas pada registration dan mobile sync.
- Endpoint mobile memakai Sanctum dan rate limit untuk login/sync.
- Query publik utama memakai `inApprovedBatch()` dan controller publik tidak mengirim raw NIK.
- Ada policy, service layer workflow, snapshot/versioning, dan audit trail sebagai fondasi governance.
- PHP syntax, route registration, dan Vite production build berhasil.

## Rencana perbaikan berurutan

### P0 — sebelum kode/deployment dibagikan

1. Revoke/rotate bearer token pada `.blackboxcli/settings.json`; bersihkan Git history dan jalankan secret scan.
2. Pastikan production debug mati; audit semua `.env`/CI secret.
3. Hentikan seeder akun default dan amankan backup JSON/PII.
4. Perbaiki urutan scope UserScope dan cache AI lintas user.

### P1 — sebelum uji penerimaan

1. Buat test profile SQLite in-memory dan profile MySQL integration.
2. Bungkus mobile push dalam transaksi/staging atomik dan sanitasi error.
3. Perbaiki mapping role, statistik `kecamatan`, bucket umur 65+, dan batas `per_page`.
4. Masking PII dan review kontrak API/mobile berdasarkan kebutuhan akses.

### P2 — hardening operasional

1. Batasi CORS, hapus endpoint test AI dari production, dan tambahkan feature flags.
2. Terapkan CI: PHP lint, Pint, PHPUnit, Vite build, `composer audit`, npm audit, secret scan.
3. Pecah worktree menjadi commit tematik dan dokumentasikan deployment, queue worker, backup, restore, serta incident response.

## Batasan audit

Audit ini tidak mengubah kode aplikasi selain menambahkan laporan ini. `composer audit` tidak dapat menyelesaikan pemeriksaan karena koneksi Packagist diblokir oleh environment. Test integrasi penuh belum dapat memvalidasi perilaku database karena service MySQL lokal tidak aktif; angka kegagalan di atas harus diulang setelah test database diisolasi.
