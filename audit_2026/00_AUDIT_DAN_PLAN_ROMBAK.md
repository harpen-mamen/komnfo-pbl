# AUDIT & RENCANA ROMBAK TOTAL
## SIPANDU -> Sistem Satu Data Indonesia Kabupaten Kepulauan Sangihe

**Tanggal Audit**: 17 Oktober 2026
**Auditor**: Claude Code
**Sistem saat ini**: SIPANDU v2.x (Laravel 10 + Filament 2 + Next.js + Flutter)
**Target**: Compliance penuh dengan Perpres 39/2019 + Perbup Sangihe 30/2024

---

## A. KONDISI SISTEM SAAT INI

### A.1 Statistik Codebase

| Metrik | Nilai |
|---|---|
| Total Tabel Database Aktual | 46 |
| Total Migrations | 86 |
| Total Models | 57 |
| Total Seeders | 18 |
| Total Filament Resources | 14 |
| Total Filament Pages | 17 |
| Total Services | 20+ |
| Total Policies | 16 |
| Total Roles di Enum | 70+ |
| Total Routes API | 69 |
| Dataset aktif | 82 |
| Dataset Fields aktif | 566 |
| Dataset Values | 0 |
| Submission Batches | 0 |

---

## B. TEMUAN AUDIT

### B.1 Aspek Yang Sudah Baik
- Generic Dataset Engine (datasets, dataset_fields, dataset_values long-format JSON)
- Spatie RBAC dengan 70+ role
- SubmissionBatch state machine (draft -> pending -> in_review -> approved/rejected/revision/archived)
- SoftDeletes everywhere
- Immutable audit_trails
- Mobile offline-first
- Multi-panel Filament
- API publik Next.js frontend
- Forum SDI structure
- Kode referensi SDI

### B.2 Masalah KRITIS

C1. Tabel Legacy Masih Bercampur
- 14+ tabel redundan dengan dataset_values (data_kesehatan, data_pendidikan, bayi, ibu_hamil, dll)

C2. Dual Workflow Engine
- Legacy: submission_batches + state machine
- SDI: workflow_logs (baru) - 2-duanya aktif tanpa konsolidasi

C3. Tidak Compliance dengan Perbup Sangihe 30/2024
- Pasal 4 - 5 Elemen Standar Data -> TIDAK ada di dataset_fields
- Pasal 8 - 4 Penyelenggara SDI -> Tidak ada role generik jelas
- Pasal 9 - Pembina Data (BPS, DPUPR) -> TIDAK ADA role spesifik
- Pasal 21 - Portal Satu Data -> TIDAK ADA portal proper

C4. Role Terlalu Banyak (70+)
- Enum AppRole punya banyak role spesifik (admin_dinkes, operator_dinkes, dst.)
- Perbup hanya butuh 4 role utama

C5. Dataset Definition Tidak Sinkron dengan 93 Tabel Sektoral Sangihe
- 88 dataset didefinisikan via seeder
- TIDAK ADA yang sesuai format spesifik 93 Tabel Sektoral Sangihe

C6. Tidak Ada Portal Publik Sesuai Pasal 21
- Frontend Next.js hanya menampilkan statistik agregat
- Tidak ada pencarian dataset, metadata, jadwal rilis, tracking download

---

## C. RENCANA ROMBAK TOTAL

### C.1 Target Akhir

| Aspek | Target |
|---|---|
| Total Tabel DB | 9 tabel (7 core + 2 pendukung) |
| Total Models | 9 Model |
| Total Roles | 8 role (generic, sesuai Perbup) |
| Total Dataset | 93 dataset (sinkron dengan Buku Sektoral 2024) |
| Compliance Perpres 39/2019 | 100% |
| Compliance Perbup 30/2024 | 100% |
| Portal Publik | portal.sangihekab.go.id |

### C.2 Schema Baru (9 Tabel)

TABEL INTI SDI (7 tabel):
1. opds                     (Master 32 OPD)
2. datasets                 (93 definisi dataset)
3. dataset_fields           (metadata + 5 elemen Perbup)
4. dataset_categories       (32 urusan pemerintah)
5. geografi_units           (Kec/Desa/Kampung self-ref)
6. data_values              (long format, JSON)
7. workflow_logs            (audit trail workflow)

TABEL PENDUKUNG (2 tabel):
8. users                    (multi-role)
9. kode_referensis          (KBLI, ICD-10, dll)

### C.3 Pemetaan Tabel Lama -> Baru

| Tabel Lama | Tujuan | Alasan |
|---|---|---|
| kabupatens, kecamatans, desas, dusuns | -> geografi_units (self-ref) | Konsolidasi |
| penduduk, keluarga, rumah, rumah_keluarga | -> data_values (ref type) | Master reference jadi dataset |
| submission_batches, submission_batch_items | -> data_values + workflow_logs | Gabung jadi single workflow |
| audit_trails | -> workflow_logs | Audit sudah di workflow_logs |
| data_kesehatan_bulanans, data_pendidikan_bulanans, bayi, ibu_hamil, dll | -> data_values (long format) | 14+ tabel redundant |

### C.4 Tahapan Eksekusi

FASE 1: BACKUP & DOKUMENTASI (5 menit)
- backup/kominfo_full_backup_YYYYMMDD_HHMMSS.sql
- docs/AUDIT_DAN_PLAN_ROMBAK.md
- docs/ARSITEKTUR_SDI_v3.md

FASE 2: PEMBERSIHAN LEGACY (30 menit)
- Hapus 14+ tabel legacy via migration
- Hapus model-model terkait
- Hapus 7+ console commands
- Hapus 11+ policies yang tidak relevan
- Hapus 100+ Filament Resources di _archived

FASE 3: SCHEMA BARU + SDI METADATA (60 menit)
- Migration: tambah 5 elemen Standar Data di dataset_fields
- Migration: tambah dataset_categories
- Migration: tambah geografi_units self-ref
- Migration: tambah role baru sesuai Perbup
- Migrate role lama ke role baru

FASE 4: SEEDER 93 TABEL SEKTORAL SANGIHE (90 menit)
- Seed 93 dataset dari Buku Statistik Sektoral 2024
- Seed 32 urusan pemerintah (dataset_categories)
- Seed 5+ role generik SDI
- Seed 32+ user demo (1 per OPD)

FASE 5: FORM GENERATOR + WORKFLOW (60 menit)
- Enhance DynamicDatasetResource untuk JSON long-format
- Tambah form multi-step untuk tiap dataset
- Workflow automation (Plan -> Submit -> Verify -> Publish)
- Notifikasi ke Walidata

FASE 6: PORTAL SATU DATA PUBLIK (60 menit)
- Next.js: Halaman /satu-data
- Next.js: Halaman /dataset/[slug]
- API: GET /api/v2/satudata/datasets
- API: GET /api/v2/satudata/datasets/{id}/values
- API: GET /api/v2/satudata/kode-referensi

FASE 7: TESTING & DOKUMENTASI (30 menit)
- php artisan test (unit + feature)
- Manual test di browser
- Laporan Rombak

---

## D. STRATEGI EKSEKUSI

### D.1 Pendekatan: REFACTOR (Bukan Rewrite)

Saya TIDAK rewrite codebase. Saya:
1. Hapus legacy tables (aman via migration down)
2. Tambah field SDI compliance
3. Restructure data seeder dengan 93 dataset Sektoral
4. Enhance Generic Dataset Engine yang sudah kuat
5. Build Portal Publik baru sebagai halaman tambahan

### D.2 Jaminan Tidak Ada Data Hilang

- Semua tabel legacy yang berisi data TIDAK dihapus via migration
- Hanya tabel legacy yang sudah kosong atau sudah jadi dataset_values saja yang dihapus
- Backup full SQL disimpan di backups/

### D.3 Format Compliance

Setiap migration baru akan mengikuti standar Perbup 30/2024:
- Field untuk Konsep, Definisi, Klasifikasi, Ukuran, Satuan
- Workflow 4 tahap (Plan, Submit, Verify, Publish)
- 5 Elemen Standar Data

---

**Status**: Plan ini akan dieksekusi bertahap.
