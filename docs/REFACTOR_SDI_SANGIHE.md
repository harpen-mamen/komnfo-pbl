# SIPANDU - Sistem Pemerintahan Digital Terpadu
## Dokumentasi Arsitektur Database & Implementasi

**Versi: 2.0 (Refactor SDI) | Oktober 2026**  
**Untuk: Kabupaten Kepulauan Sangihe**

---

## 📚 Daftar Isi

1. [Pengenalan](#1-pengenalan)
2. [Arsitektur Database](#2-arsitektur-database)
3. [Engine Generik](#3-engine-generik)
4. [Dataset per OPD](#4-dataset-per-opd)
5. [Compliance SDI](#5-compliance-sdi)
6. [Setup & Instalasi](#6-setup--instalasi)
7. [Mengelola Dataset](#7-mengelola-dataset)
8. [Tambah OPD/Dataset Baru](#8-tambah-opddataset-baru)
9. [Migrasi Data Lama](#9-migrasi-data-lama)
10. [FAQ](#10-faq)

---

## 1. Pengenalan

### Apa itu SIPANDU?

SIPANDU adalah **platform data terpadu** untuk Pemerintah Kabupaten Kepulauan Sangihe.
Sistem ini dibangun berbasis **Satu Data Indonesia (SDI)** sesuai **Perpres 39/2019**.

### Prinsip Utama

- ✅ **Satu sumber kebenaran** - tidak ada data silos antar OPD
- ✅ **TIDAK ADA tabel hardcoded per domain** - semua data lewat engine generik
- ✅ **Setiap OPD = konfigurasi dataset** - bukan developer yang bikin tabel
- ✅ **Compliance SDI** - metadata, kode referensi, klasifikasi standar

### Statistik

| Metrik | Nilai |
|---|---|
| Total tabel database | **28** (turun 57% dari 65) |
| Total OPD | **32** |
| Total dataset siap pakai | **88** |
| Total field | **604** |
| Tabel sektoral hardcoded | **0** (semua jadi dataset_values) |

---

## 2. Arsitektur Database

### 2.1 ERD Sederhana

```
                       ┌────────────────────────┐
                       │         USERS          │ ← Spatie RBAC
                       │     + ROLES/PERMS      │
                       └────────────────────────┘
                                  │
                                  ▼
              ┌─────────────────────────────────────────┐
              │            OPDS (32 OPD)                │
              └─────────────────────────────────────────┘
                                  │
                                  ▼
       ┌──────────────────────────────────────────────────┐
       │           WILAYAHS (self-ref)                    │
       │   kabupaten → kecamatan → desa → dusun           │
       └──────────────────────────────────────────────────┘

       ┌────────────────────────┐    ┌──────────────────────┐
       │  KODE_REFERENSIS       │    │   FORUM_SATU_DATA    │
       │  (KBLI, ICD-10, dll)  │    │  (FSDK sesuai        │
       │  master klasifikasi   │    │  Perpres 39/2019)    │
       └────────────────────────┘    └──────────────────────┘

       ┌─────────────────────────────────────────────┐
       │              DATA ENGINE                    │
       ├─────────────────────────────────────────────┤
       │  DATASETS (definisi + SDI metadata)         │
       │    ↓                                         │
       │  DATASET_FIELDS (schema kolom dinamis)      │
       │    ↓                                         │
       │  DATASET_VALUES ← SEMUA DATA ADA DI SINI    │
       │  (JSON values, polymorphic)                  │
       └─────────────────────────────────────────────┘

       ┌─────────────────────────────────────────────┐
       │            SPATIAL ENGINE                    │
       ├─────────────────────────────────────────────┤
       │  OBJEK_LAPANGAN_TYPES (konfigurator)         │
       │  OBJEK_LAPANGANS (RUMAH, SEKOLAH, JALAN,    │
       │  PELABUHAN, PULAU, dll - SEMUA SPASIAL)     │
       └─────────────────────────────────────────────┘

       ┌─────────────────────────────────────────────┐
       │            WORKFLOW ENGINE                  │
       ├─────────────────────────────────────────────┤
       │  SUBMISSION_BATCHES (semua kiriman)         │
       │  SUBMISSION_BATCH_ITEMS (polymorphic)       │
       │  WORKFLOW_LOGS (history transisi)           │
       │  PERIODE_PELAPORANS (master periode)        │
       └─────────────────────────────────────────────┘

       ┌─────────────────────┐  ┌──────────────────────┐
       │  PENDUDUKS (NIK)    │  │   KELUARGAS (KK)     │
       │  master reference   │  │   master reference   │
       └─────────────────────┘  └──────────────────────┘

       ┌─────────────────────────────────────────────┐
       │  PAGES + PAGE_MEDIA (CMS generik)           │
       │  AUDIT_TRAILS, NOTIFICATIONS, IMPORT_LOGS   │
       │  AI_CONVERSATIONS, MOBILE_SYNC_OPERATIONS   │
       └─────────────────────────────────────────────┘
```

### 2.2 Daftar 28 Tabel

| # | Tabel | Fungsi |
|---|---|---|
| **CORE** |||
| 1 | `users` | Akun pengguna |
| 2 | `opds` | 32 Organisasi Perangkat Daerah |
| 3 | `wilayahs` | Geografis (self-ref: kab→kec→desa→dusun) |
| 4-8 | `roles`, `permissions`, `model_has_*`, `role_has_*` | RBAC Spatie (5 tabel) |
| **SDI STANDARDS** |||
| 9 | `kode_referensis` | KBLI, ICD-10, KBJI, Wilayah BPS |
| 10-11 | `forum_satu_data`, `forum_strukturs` | FSDK + struktur organisasi |
| **DATA ENGINE** |||
| 12 | `datasets` | Definisi dataset + SDI metadata |
| 13 | `dataset_fields` | Schema kolom dinamis |
| 14 | `dataset_values` | **SEMUA data** dari semua OPD |
| 15 | `objek_lapangan_types` | Konfigurator tipe spasial |
| 16 | `objek_lapangans` | **SEMUA titik spasial** |
| **WORKFLOW** |||
| 17 | `submission_batches` | Engine pengajuan |
| 18 | `submission_batch_items` | Item polymorphic |
| 19 | `workflow_logs` | History workflow (gabungan) |
| 20 | `periode_pelaporans` | Master periode |
| **MASTER** |||
| 21 | `penduduks` | Data NIK |
| 22 | `keluargas` | Data KK |
| **CONTENT** |||
| 23 | `pages` | CMS generik |
| 24 | `page_media` | Media/gallery |
| **SYSTEM** |||
| 25 | `audit_trails` | Log + registrasi + kendala |
| 26 | `notifications` | Notifikasi |
| 27 | `import_logs` | Import + kendala |
| 28 | `ai_conversations` + `ai_messages` | AI |
| 29 | `mobile_sync_operations` | Sync mobile |
| 30-33 | `jobs`, `failed_jobs`, `personal_access_tokens`, `password_reset_tokens` | Laravel core (4) |

---

## 3. Engine Generik

### 3.1 Kenapa Engine Generik?

**Sebelum (❌):**
- Setiap OPD butuh dataset baru → developer bikin tabel baru → migration → model → resource
- Butuh waktu 1-2 minggu per dataset
- Tabel membengkak (65 tabel)
- Inkonsistensi format data

**Sesudah (✅):**
- Tambah dataset = insert ke `datasets` + `dataset_fields` (via UI / seeder)
- Butuh waktu 5 menit per dataset
- Tabel tetap (28 tabel)
- Format data konsisten via JSON schema

### 3.2 Cara Kerja

```
1. ADMIN OPD / WALIDATA
   - Buka menu "Kelola Dataset"
   - Klik "Tambah Dataset"
   - Isi: kode, nama, period_type, dll
   - Tambah fields sesuai kebutuhan
   
2. SISTEM
   - Simpan ke tabel `datasets` + `dataset_fields`
   - Otomatis generate form input di panel
   - Otomatis generate tabel daftar data
   
3. OPERATOR OPD
   - Buka menu "Data Sektoral"
   - Pilih dataset
   - Isi form (auto-generated)
   - Submit → masuk ke `submission_batches`
   
4. VALIDATOR
   - Review di `submission_batches`
   - Approve / Reject / Minta Revisi
   - Status berubah di `workflow_logs`
```

### 3.3 Polymorphic Reference

Field dengan tipe `reference` bisa merujuk ke:
- `wilayahs` - kecamatan/desa/dusun
- `penduduks` - data NIK
- `keluargas` - data KK
- `objek_lapangans` - titik spasial
- `opds` - organisasi
- `kode_referensis` - master klasifikasi
- `periode_pelaporans` - master periode

Contoh konfigurasi field:
```php
[
    'nik' => [
        'label' => 'NIK Penduduk',
        'tipe' => 'reference',
        'reference_target' => 'penduduks',
        'required' => true,
    ],
    'puskesmas_id' => [
        'label' => 'Puskesmas',
        'tipe' => 'reference',
        'reference_target' => 'objek_lapangans',
    ],
]
```

---

## 4. Dataset per OPD

Setiap OPD punya dataset yang sudah di-predefined di seeder.
Lihat `database/seeders/data/SangiheOpdDatasetsPart{1,2,3}.php` untuk detail.

### Ringkasan Dataset

| OPD | Dataset | Khas |
|---|---|---|
| DINKES | 6 | Yankes, SDM, Stok Obat, Ibu Hamil, Bayi, Surveilans |
| DISDIK | 5 | Rekap Sekolah, GTK, Siswa, Kinerja, Guru Individu |
| DPUPR | 4 | Jalan, Jembatan, Pemeliharaan, Air Bersih |
| PERIKANAN | 4 | **Tangkap, Nelayan, Kapal, Budidaya** 🌊 |
| BATAS | 3 | **Pulau Kecil, Infrastruktur Batas, Kegiatan** 🏝️ |
| DINSOS | 3 | PMKS, Bansos, Lansia/Disabilitas |
| DISHUB | 4 | **Pelabuhan, Kapal Laut, Lalin, Kecelakaan** ⚓ |
| DLH | 3 | TPS, Volume Sampah, Kualitas Air |
| ... | ... | (lihat blueprint lengkap) |

---

## 5. Compliance SDI

Sesuai **Perpres 39/2019 tentang Satu Data Indonesia**:

### 5.1 Tabel Kode Referensi (`kode_referensis`)

Berisi klasifikasi standar nasional:
- `kode_wilayah` - Kodefikasi Wilayah BPS
- `kbli` - Klasifikasi Baku Lapangan Usaha Indonesia
- `icd10` - Klasifikasi Penyakit Internasional
- `icd9` - Tindakan Medis
- `kbji` - Klasifikasi Baku Jabatan Indonesia
- `kode_pendidikan` - Jenjang pendidikan
- `agama`, `pekerjaan` - Master demografi

### 5.2 Metadata Dataset

Setiap dataset punya metadata SDI:
- Judul, Deskripsi
- Produsen (OPD)
- Penanggung Jawab + NIP
- Kontak Produsen
- Periode Update, Frekuensi
- Cakupan Wilayah (Kab/Kec/Desa)
- Bahasa, Format Data
- Lisensi (CC BY 4.0, Terbatas, Internal)
- Standar Klasifikasi
- Tags
- URL Akses
- Status (Aktif/Arsip/Eksperimental)

### 5.3 Forum Satu Data (FSDK)

Struktur FSDK sesuai Perpres 39/2019:
- **Pembina**: Bupati
- **Pengarah**: Wakil Bupati
- **Penanggung Jawab**: Sekda
- **Ketua**: Ka Bappelitbang
- **Koordinator**: Ka Diskominfo (Walidata)
- **Anggota**: 32 Kepala OPD (Produsen Data)
- **Sekretariat**: Bappelitbang

> ⚠️ **Sesuaikan** `no_sk`, nama pejabat, NIP dengan SK Bupati asli di file
> `database/seeders/ForumSatuDataSangiheSeeder.php`

---

## 6. Setup & Instalasi

### 6.1 Migrasi Database

```bash
# 1. Backup database lama (PENTING!)
mysqldump -u root kominfo > backup_before_refactor.sql

# 2. Jalankan migrasi
php artisan migrate

# 3. Jalankan seeder baru
php artisan db:seed --class=OpdDatasetSangiheSeeder
php artisan db:seed --class=KodeReferensiSdkiSeeder
php artisan db:seed --class=ForumSatuDataSangiheSeeder
php artisan db:seed --class=ObjekLapanganTypeSangiheSeeder

# Atau sekaligus via DatabaseSeeder
php artisan db:seed

# 4. Verifikasi
php artisan tinker
> \App\Models\Dataset::count()        // harus 88
> \App\Models\DatasetField::count()   // harus 604
> \App\Models\KodeReferensi::count()  // harus 50+
> \App\Models\ForumSatuData::count()   // harus 1
> \App\Models\ObjekLapanganType::count() // harus 50+
```

### 6.2 Migrasi Data Lama (ETL)

Jika ada data di tabel lama yang ingin dimigrasi:

```bash
php artisan data:migrate-old-to-new
```

Lihat `STEP 3` di bawah untuk detail.

---

## 7. Mengelola Dataset

### 7.1 Via Panel (Filament)

1. Login ke `/admin` sebagai super_admin atau admin_opd
2. Buka menu **"Data Sektoral"**
3. Pilih dataset → klik **"Input Data Baru"**
4. Isi form (auto-generated dari `dataset_fields`)
5. Submit

### 7.2 Via Tinker / Code

```php
// Tambah dataset
$ds = Dataset::create([
    'opd_id' => 1, // DINKES
    'kode' => 'DATASET_BARU',
    'nama' => 'Dataset Contoh Baru',
    'period_type' => 'bulanan',
    'kategori' => 'contoh',
    'created_by' => 1,
]);

// Tambah field
DatasetField::create([
    'dataset_id' => $ds->id,
    'nama' => 'kolom1',
    'label' => 'Kolom 1',
    'tipe' => 'string',
    'required' => true,
    'urutan' => 0,
]);

// Input data
DatasetValue::create([
    'dataset_id' => $ds->id,
    'values' => ['kolom1' => 'nilai contoh'],
    'status_verifikasi' => 'draft',
    'created_by' => 1,
]);
```

---

## 8. Tambah OPD/Dataset Baru

### 8.1 Tambah OPD Baru

Tidak perlu migration! Cukup insert ke `opds`:

```php
Opd::create([
    'kode' => 'OPD_BARU',
    'nama' => 'Dinas Baru',
    'singkatan' => 'Dinasbaru',
    'kategori' => 'kesehatan',
    'is_verifikator' => false,
]);
```

### 8.2 Tambah Dataset Baru untuk OPD Existing

Edit file blueprint di `database/seeders/data/SangiheOpdDatasetsPart{1,2,3}.php`:

```php
'OPD_EXISTING' => [
    'datasets' => [
        'DATASET_BARU' => [
            'nama' => 'Nama Dataset',
            'deskripsi' => 'Deskripsi',
            'period_type' => 'bulanan',
            'kategori' => 'kategori',
            'fields' => [
                'field1' => [
                    'label' => 'Field 1',
                    'tipe' => 'string',
                    'required' => true,
                ],
                'field2' => [
                    'label' => 'Field 2',
                    'tipe' => 'enum',
                    'options' => ['A', 'B', 'C'],
                ],
            ],
        ],
    ],
],
```

Lalu jalankan:
```bash
php artisan db:seed --class=OpdDatasetSangiheSeeder
```

Atau via UI: buka **"Dataset Resource"** di panel admin (jika diaktifkan).

---

## 9. Migrasi Data Lama

Lihat `STEP 3` di bawah atau baca dokumentasi ETL.

---

## 10. FAQ

### Q: Kenapa tabel lama dihapus?
**A:** Karena tabel lama bersifat **sectoral-specific** (kesehatan, pendidikan, dll).
Tabel baru bersifat **generic** (semua data lewat `dataset_values`).
Hasilnya: tidak ada duplikasi, lebih mudah maintenance.

### Q: Bagaimana dengan data existing?
**A:** Gunakan command `php artisan data:migrate-old-to-new` untuk ETL.
Atau input ulang via panel jika data tidak banyak.

### Q: Apakah setiap OPD harus pakai dataset yang sudah didefine?
**A:** Tidak. OPD bisa **tambah dataset sendiri** via blueprint atau UI (akan datang).

### Q: Kenapa pakai JSON untuk `values`?
**A:** Agar schema field bisa dinamis. Trade-off: query field spesifik sedikit
lebih lambat, tapi indexed di `dataset_id` + `tahun` + `periode_label`.

### Q: Bagaimana cara query data tertentu (mis: semua puskesmas di Kec. A)?
**A:** 
```php
DatasetValue::where('dataset_id', $datasetId)
    ->whereJsonContains('values->kecamatan_id', $kecamatanId)
    ->get();
```

### Q: Apakah Perpres 39/2019 sudah sepenuhnya comply?
**A:** Untuk MVP: ✅. Untuk compliance penuh: tambah metadata ke portal publik,
   integrasi ke SIPDT Nasional, audit metadata.

### Q: Siapa yang bisa edit dataset?
**A:** Hanya **super_admin** dan **walidata** (Koordinator Satu Data).
   Produsen data (operator OPD) hanya bisa input **values**, bukan ubah schema.

---

## 📞 Kontak

Untuk pertanyaan teknis, hubungi Tim Diskominfo Sangihe.

---

**Dokumen ini akan di-update seiring perkembangan sistem.**