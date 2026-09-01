
# ARSITEKTUR PLATFORM DATA PEMERINTAHAN DIGITAL KABUPATEN KEPULAUAN SANGIHE
## SIPANDU - Sistem Informasi Pangkalan Data Terpadu

- Versi: 2.0 | Tanggal: 8 Agustus 2026
- Status: Arsitektur Target
- Platform: Laravel 10 + Filament 2 + Flutter Mobile

---

## 1. GAMBARAN BESAR

### 1.1 Visi Platform

SIPANDU adalah Government Data Platform tingkat kabupaten, bukan kumpulan aplikasi OPD terpisah.

Tujuan utama:
- Satu sumber data kebenaran (Single Source of Truth) untuk seluruh data pemerintahan
- Setiap data resmi wajib melewati workflow verifikasi sebelum dipublikasikan
- OPD adalah produsen dan pemilik dataset, Kominfo adalah penjaga kualitas dan gateway publikasi
- Mobile app memungkinkan input data offline-first di tingkat desa
- Dashboard eksekutif tersedia untuk bupati, wakil bupati, dan kepala OPD

### 1.2 Prinsip Arsitektur

| Prinsip | Penjelasan |
|---------|-----------|
| Platform, bukan aplikasi OPD | OPD punya dataset, bukan schema database sendiri |
| Master data bersama | Wilayah, penduduk, keluarga, rumah = data bersama semua OPD |
| Official data by workflow | Input tidak otomatis menjadi data resmi |
| Single sign-on | Satu akun untuk semua panel |
| Offline-first mobile | Mobile dirancang untuk koneksi terputus |
| Separation of concerns | Write model (operasional) tidak sama dengan Read model |
| API-first | Kontrak API versioning untuk semua integrasi |
| Least privilege | Setiap role hanya melihat data wewenangnya |

### 1.3 Struktur Pemerintahan Sangihe

BUPATI & WAKIL BUPATI (Dashboard Eksekutif)
  |-- DINAS KOMINFO (Gateway & Verifikasi Final)
  |-- 20 DINAS/BADAN (OPD) - Produsen Dataset
  |     |-- Admin OPD + Operator OPD + Kepala Dinas
  |-- 15 KECAMATAN
        |-- Admin Kecamatan
              |-- 197 DESA
                    |-- Admin Desa (Mobile App)

---

## 2. ALUR DATA END-TO-END

### 2.1 Tiga Jalur Input Data

**JALUR 1: WILAYAH (Desa -> Kecamatan -> Kominfo)**
Admin Desa (Mobile App) input: Rumah, Keluarga, Penduduk -> Sync ke Server -> Batch Draft -> Kirim ke Kecamatan -> Admin Kecamatan merangkum data dari semua desa -> Kirim ke Kominfo -> Batch Pending Review

**JALUR 2: OPD (Langsung Input)**
Admin/Operator OPD input data sektoral sesuai OPD -> Batch OPD Draft -> Verifikasi Internal (Kepala Dinas) -> Batch Approved OPD -> Kirim ke Kominfo

**JALUR 3: Verifikasi Silang OPD**
Data dari Kecamatan masuk ke verifikasi OPD terkait:
- DUKCAPIL -> verifikasi NIK/akta
- DISDIK -> verifikasi NPSN/guru
- DINKES -> verifikasi faskes
- DPUPR -> verifikasi infrastruktur jalan
- DLH -> verifikasi TPA/Sampah
- DISTAN -> verifikasi lahan
- DISPERINDAG -> verifikasi pasar/UMKM
- BPBD -> verifikasi bencana
Setelah verifikasi OPD -> Forward ke Kominfo

### 2.2 Workflow Status Batch

  DRAFT --Kirim--> PENDING --Mulai Review--> IN_REVIEW
                                    |
                    +---------------+---------------+
                    |               |               |
                    v               v               v
                APPROVED       REVISION       REJECTED
                    |               |               v
                    v               |            ARCHIVED
              PUBLISHED             |
              dashboard,            |
              portal publik,         |
              API/SIPDT             |
                    |        +------+
                    v        | kembali ke PENDING
                ARCHIVED   (kirim ulang)

### 2.3 Aturan Transisi Status

| Status Awal | Aksi | Status Baru | Actor |
|------------|------|------------|-------|
| DRAFT | Kirim | PENDING | Admin Desa / Admin Kecamatan |
| PENDING | Mulai Review | IN_REVIEW | Admin Kecamatan / Admin Kominfo |
| IN_REVIEW | Setuju | APPROVED | Admin Kominfo |
| IN_REVIEW | Minta Revisi | REVISION | Admin Kominfo / OPD Verifikator |
| IN_REVIEW | Tolak | REJECTED | Admin Kominfo |
| REVISION | Kirim Ulang | PENDING | Admin Desa / Admin Kecamatan |
| APPROVED | Arsipkan | ARCHIVED | Admin Kominfo |
| REJECTED | Arsipkan | ARCHIVED | Admin Kominfo |

---

## 3. STRUKTUR ROLE & AKSES

### 3.1 Hierarki Role (5 Tier)

**TIER 1: SUPER ADMIN**
full system access, manage all users & OPDs

**TIER 2: ADMIN KOMINFO (KABUPATEN)**
view all batches from all kecamatan, approve/reject, publish to public portal, manage wilayah master data, OPD configurations

**TIER 3: ADMIN OPD (DINAS/BADAN)**
- admin_[kode_opd] -> full OPD data access
- kepala_dinas_[kode] -> approve OPD batch internally
- operator_[kode_opd] -> input data only

**TIER 4: ADMIN KECAMATAN**
compile/aggregate batch from all desas in their kecamatan, forward to Kominfo, request revision on desa batches

**TIER 5: ADMIN DESA (MOBILE)**
input rumah, keluarga, penduduk for their desa, create draft batch, submit to kecamatan

### 3.2 Matriks Akses

| Role | Input Data | Kirim Batch | Review | Approve | Publish | View All |
|------|-----------|-------------|--------|---------|---------|---------|
| super_admin | Ya (all) | Ya | Ya | Ya | Ya | Ya |
| admin | Ya (all) | Ya | Ya | Ya | Ya | Ya |
| admin_kominfo | View only | - | Ya | Ya | Ya | Ya |
| admin_[opd] | OPD sendiri | Ya | Ya* | - | - | - |
| operator_[opd] | OPD sendiri | Ya | - | - | - | - |
| kepala_dinas_[opd] | OPD sendiri | - | Ya | Ya | - | - |
| admin_kecamatan | Wilayah sendiri | Ya | Ya** | - | - | - |
| admin_desa | Desa sendiri | Ya | - | - | - | - |
| bupati / wakil_bupati | Dashboard only | - | - | - | - | - |
| kepala_dinas | Dashboard only | - | - | - | - | - |

* Admin OPD hanya review batch yang masuk ke OPD-nya untuk verifikasi silang
** Admin Kecamatan review batch yang dibuat oleh Admin Desa di kecamatannya

### 3.3 Role-Based Navigation

| Role | Menu yang Ditampilkan |
|------|----------------------|
| Super Admin / Admin | Dashboard, Wilayah, Semua modul data, Verifikasi Batch, User Management, OPD Management, Audit Trail |
| Admin Kominfo | Dashboard Kominfo, Monitoring Semua Kecamatan, Verifikasi Batch, Wilayah, Data Master, Audit Trail |
| Admin OPD | Dashboard OPD, Modul data sesuai OPD, Kirim Batch OPD, Monitoring submission |
| Admin Kecamatan | Dashboard Kecamatan, Input Data, Kirim Batch ke Kominfo, Monitoring submission |
| Admin Desa (Mobile) | Dashboard Desa, Input Rumah/Keluarga/Penduduk, Kirim Draft ke Kecamatan |

---

## 4. ARSITEKTUR TEKNIS

### 4.1 Tech Stack

FRONTEND:
- Web Admin: Filament 2.x + Tailwind CSS + Chart.js
- Mobile App: Flutter 3.x + BLoC + Drift (SQLite)
- Public Portal: Laravel Blade + Alpine.js + Tailwind

BACKEND:
- Framework: Laravel 10.x
- Panel Admin: Filament 2.x
- Database: MySQL 8.0 / PostgreSQL 15
- Cache: Redis (optional)
- Queue: Laravel Queue (database driver default)
- Auth: Laravel Sanctum + Spatie Permission
- PDF: Barryvdh DomPDF
- Excel: Maatwebsite Excel

INFRASTRUCTURE:
- Server: VPS / Shared Hosting (awal)
- Domain: sipandu.sangihekab.go.id
- SSL: Lets Encrypt
- Backup: Daily automated + manual on major ops
- Monitoring: Laravel Telescope (dev) / Sentry (prod)

### 4.2 Struktur Aplikasi

app/
  Models/         Semua model (flat saat ini, target: Contexts/)
  Services/       Cross-context services
  Filament/
    Resources/    CRUD resources per entity
    Pages/        Custom pages
    Widgets/      Dashboard widgets
  Support/        PanelAccess, traits, helpers
  Enums/          Shared enums
  Notifications/  Notification classes

TARGET (refactor bertahap per fase):
app/Contexts/
  Identity/       Users, Roles
  Wilayah/        Kabupaten, Kecamatan, Desa, Rumah, Keluarga, Penduduk
  Sectoral/       Data OPD (Kesehatan, Pendidikan, PU, dll.)
  Workflow/       SubmissionBatch, BatchItem, WorkflowHistory
  Analytics/      Dashboard aggregation
  Publication/    Public portal, API
  Audit/          AuditTrail, notifications

### 4.3 Read Model vs Write Model

WRITE PATH (Admin Panel):
Input Form -> Validation -> Entity Model -> SubmissionBatch -> Workflow -> Status Change + Notification

READ PATH (Dashboard / Portal Publik):
AggregatedQuery (only APPROVED batches) -> DashboardWidget -> Charts/Maps/Tables -> PublicPortal /api/v1/

PRINSIP KRUSIAL:
Query dashboard, analytics, dan portal publik HANYA membaca data dari
SubmissionBatch yang berstatus APPROVED / PUBLISHED.
Data draft/tidak terverifikasi TIDAK BOLEH muncul di dashboard agregat.

---

## 5. STRUKTUR DATABASE

### 5.1 Entity Relationship (Core)

kabupaten
  +-- kecamatan (1:N)
          +-- desa (1:N)
                  +-- rumah (1:N)
                          +-- keluarga (1:N)
                                  +-- penduduk (N)

submission_batches
  +-- submission_batch_items (1:N)
  +-- workflow_instances (1:N)
  +-- workflow_histories (1:N)

### 5.2 Tabel Inti Workflow

submission_batches:
  - id, module (rumah/keluarga/penduduk/kesehatan/mixed)
  - submission_number (contoh: SB-KESEHATAN-KEC-3-B08-T2026-001)
  - kecamatan_id, opd_id (NULL untuk batch kecamatan)
  - periode_bulan, semester, periode_tahun
  - package_signature (hash untuk deteksi duplikat)
  - package_keys (array modul)
  - package_summary (ringkasan per modul)
  - status (draft/pending/in_review/approved/rejected/revision/archived)
  - notes, created_by, submitted_at, verified_at, verified_by
  - payload_snapshot (JSON snapshot lengkap data saat submit)
  - version (auto increment per status change)

submission_batch_items:
  - id, submission_batch_id, package_key, entity_type, entity_id

---

## 6. MODUL DATA OPD

### 6.1 Klasifikasi Data

| Kategori | Sumber | Verifikasi |
|---------|--------|-----------|
| Data Wilayah | Admin Desa (mobile) -> Admin Kecamatan | Kominfo + OPD terkait |
| Data OPD | Operator OPD langsung | Kepala Dinas -> Kominfo |
| Data Master | Super Admin / Admin | - |

### 6.2 Status Modul OPD

**Dinas Kesehatan (DINKES):**
- DataKesehatanBulanan: Bulanan | Ada
- DataIbuHamil: Bulanan | Model ada
- DataBayiBalita: Bulanan | Model ada
- DataPenyakitMenular: Bulanan | Model ada
- DataImunisasi: Bulanan | Belum
- DataObatAlkes: Bulanan | Belum
- DataTenagaKesehatan: Master | Model ada
- DataFasilitasKesehatan: Master | Belum
- DataKB: Bulanan | Belum

**Dinas Pendidikan (DISDIK):**
- DataPendidikanBulanan: Semester | Ada
- DataGuru: Master | Belum
- DataSiswa: Semester | Belum
- DataKelulusan: Semester | Belum
- DataPrasaranaSekolah: Master | Belum
- DataGTK: Tahunan | Belum
- DataBeasiswa: Tahunan | Belum

**DPUPR:** DataJalan, DataJembatan, DataDrainase, DataBangunanGedung, DataIrigasi, DataAirBersih, DataSanitasi

**DINSOS:** DataPMKS, DataBansos, DataDisabilitas, DataBencana

**DUKCAPIL:** DataKTP, DataAkta, DataPerpindahan

**OPD Lainnya:** DLH, DISTAN, DISPERINDAG, BKPSDMD, BAPPEDA, DISHUB, BPBD

---

## 7. SERVICE LAYER

### 7.1 Daftar Service

SUBMISSION WORKFLOW:
- SubmissionBatchService.php -- Buat batch, snapshot, module definitions
- BatchApprovalService.php -- Transisi status, notifikasi
- SubmissionWorkflowService.php -- Wrapper submit
- SubmissionPublishService.php -- Publish approved -> read model
- SubmissionSnapshotService.php -- Snapshot data saat submit
- SubmissionVersionService.php -- Versioning & revision

DATA CAPTURE:
- PendudukImportService.php -- Import Excel/CSV
- MobileSyncService.php -- Sync offline mobile
- DataAggregationService.php -- Aggregate approved data

ANALYTICS:
- AnalyticsService.php -- Dashboard stats, charts
- DashboardAggregationService.php -- Role-specific dashboard data

IDENTITY:
- UserRegistrationApprovalService.php

NOTIFICATION:
- BatchNotification.php

### 7.2 PanelAccess

isSuperAdmin() -- super_admin, admin

Kominfo (Kabupaten):
- isKominfoKabupaten()
- canValidateKominfo()
- canPublishBatch()

OPD:
- isOpdAdmin()
- isOpdOperator()
- getUserOpdModule()
- canApproveOpdBatch(module)

Kecamatan & Desa:
- isAdminKecamatan()
- isAdminDesa()

Eksekutif:
- isEksekutif()

Panel Visibility:
- canAccessKabupatenPanel()
- canAccessKecamatanPanel()
- canAccessOpdPanel()
- canAccessDesaPanel()
- canAccessEksekutifPanel()

---

## 8. WORKFLOW VERIFIKASI MULTI-TAHAP

### 8.1 Verifikasi Data Wilayah

ADMIN DESA -> Admin Kecamatan -> Admin Kominfo -> PUBLISHED

Admin Desa: Input data, Kirim batch
Admin Kecamatan: Terima dari semua desa, Rangkum, Kirim ke Kominfo
Admin Kominfo: Cross-check OPD (DUKCAPIL->NIK, DISDIK->NPSN, DINKES->faskes), Approve/Tolak/Revisi, publish()

### 8.2 Verifikasi Data OPD

OPERATOR OPD -> KEPALA DINAS -> ADMIN KOMINFO

Operator OPD: Input data OPD, Kirim batch
Kepala Dinas: Review internal, Approve
Admin Kominfo: Final review, Approve/Tolak, publish()

### 8.3 Cross-OPD Verification Matrix

| Data dari Kecamatan | OPD Verifikator | Field Validasi |
|--------------------|-----------------|----------------|
| Data Rumah & KK | - | Langsung ke Kominfo |
| Data Penduduk | DUKCAPIL | NIK validasi |
| Data Sekolah | DISDIK | NPSN, data guru |
| Data Puskesmas | DINKES | Kode faskes |
| Data Pasar | DISPERINDAG | Kode pasar |
| Data UMKM | DISPERINDAG | NIB |
| Data Pertanian | DISTAN | Kode lahan |
| Data Jalan Desa | DPUPR | Kode ruas |
| Data TPA/Sampah | DLH | Kode TPS |
| Data Bencana | BPBD | Kode kejadian |

---

## 9. DASHBOARD & REPORTING

### 9.1 Dashboard Role-Specific

**SUPER ADMIN / KOMINFO:**
- KPI Cards: Total Penduduk, Total KK, Total Rumah, OPD Aktif
- Charts: Pertumbuhan Penduduk, Distribusi Usia, Gender
- Maps: Peta Sebaran Penduduk per Kecamatan
- Workflow: Batch Pending / In Review / Approved / Rejected
- OPD Stats: Status Input per OPD
- Progress pelaporan periode aktif

**OPD (KEPALA DINAS / OPERATOR):**
- KPI OPD: Total Data, Progress, Periode Aktif
- Modul Data sesuai OPD
- Submission: Batch Saya, Status, Riwayat Kirim
- Perbandingan dengan periode sebelumnya
- Quick Action: Input Data, Import, Export, Kirim Batch

**KECAMATAN:**
- Stats: Total Penduduk, KK, Rumah, Progress Pendataan
- Desa Status: Progress input per desa
- Submission: Kirim Batch, Riwayat, Status
- Quick Input: Form cepat Rumah/KK/Penduduk

**EKSEKUTIF (CABINET):**
- Indikator Utama: Total Penduduk, Laju Pertumbuhan, Rasio Gender
- Sektor: Kesehatan, Pendidikan, Ekonomi, dll.
- Perbandingan: Capaian per OPD vs Target
- Trend per periode pelaporan
- Peringatan: Data belum lengkap, Batch pending

---

## 10. PORTAL PUBLIK

Portal publik hanya menampilkan data yang:
1. Berasal dari SubmissionBatch berstatus APPROVED / PUBLISHED
2. Sudah melewati periode embargo (jika ada)
3. Tidak mengandung data pribadi sensitif (NIK di-mask, dll.)

Routes:
  /                      Landing page
  /desa/[slug]           Profil desa
  /kecamatan/[slug]      Profil kecamatan
  /statistik             Statistik agregat
  /peta                  Peta interaktif
  /berita                Berita/pengumuman
  /api/v1/               Public API (future)

---

## 11. MOBILE APP

### 11.1 Tech Stack

Flutter 3.x:
- State Management: BLoC / GetX
- Local DB: Drift (SQLite)
- Auth: Laravel Sanctum (token-based)
- Sync: Custom outbox pattern
- Maps: Flutter Map + OpenStreetMap
- Charts: fl_chart
- Offline-first: Full CRUD without network

### 11.2 Mobile Data Flow

Input Form -> Local DB (Draft) -> Outbox Queue
                                         |
                                         | sync()
                                         v
                                Server API (POST /batches)
                                         |
                    +--------------------+--------------------+
                    |                    |                    |
                    v                    v                    v
              [Success]           [Conflict]            [Error]
                    |                    |                    |
                    v                    v                    v
             Mark Synced           Merge/Flag           Retry Queue

### 11.3 Mobile Modules

- Auth: Login dengan credentials
- Dashboard: Ringkasan data desa
- Rumah Capture: Input/edit data rumah
- Keluarga Capture: Input/edit data keluarga
- Penduduk Capture: Input/edit data penduduk
- Sync: Sync status & manual trigger
- Map: Tampilkan posisi rumah

---

## 12. INTEGRASI & API

### 12.1 API Endpoints

PUBLIC API (v1):
  GET  /api/v1/stats
  GET  /api/v1/penduduk
  GET  /api/v1/kecamatan
  GET  /api/v1/desa

PRIVATE API (v1) -- requires auth:
  POST /api/v1/auth/login
  POST /api/v1/batches
  GET  /api/v1/batches
  GET  /api/v1/batches/{id}
  PUT  /api/v1/batches/{id}/submit
  PUT  /api/v1/batches/{id}/approve
  PUT  /api/v1/batches/{id}/reject

MOBILE SYNC:
  POST /api/v1/mobile/sync
  GET  /api/v1/mobile/desas

### 12.2 Integrasi SIPDT Nasional

Data APPROVED -> SubmissionPublishService (transform ke format SIPDT) -> Kirim ke SIPDT Gateway Nasional

---

## 13. KEAMANAN & AUDIT

### 13.1 Audit Trail

Semua perubahan data penting dicatat:
- CREATE, UPDATE, DELETE pada entity utama
- Workflow transitions (status change)
- Login/logout attempts
- Data export/download

### 13.2 Notifikasi

Admin Desa -> Admin Kecamatan: Batch baru dari [Desa X]
Admin Desa -> Admin Kecamatan: Revisi diminta
Admin Kecamatan -> Admin Kominfo: Batch siap diverifikasi
Admin Kecamatan -> Admin Desa: Batch disetujui/ditolak
Admin Kominfo -> Admin OPD: Verifikasi silang diperlukan
Admin Kominfo -> Admin Kecamatan: Batch dipublikasikan
Sistem -> Admin Kominfo: Batch [{n}] mendekati deadline
Sistem -> Admin OPD: Periode pelaporan hampir ditutup

---

## 14. ROADMAP IMPLEMENTASI

### Fase 1: Fondasi Platform (SELESAI)
- RBAC dengan Spatie Permission
- Master wilayah (Kabupaten, Kecamatan, Desa)
- Data Rumah, Keluarga, Penduduk
- SubmissionBatch workflow
- Batch approval service
- Notifikasi database
- Audit trail
- Mobile app (capture rumah/kk/penduduk)

### Fase 2: Penyempurnaan Workflow (Prio Tinggi)
1. Cross-OPD verification routing -- Minggu 1
2. OPD batch submission (DINKES, DISDIK) -- Minggu 2
3. Kepala Dinas approval flow -- Minggu 2
4. Kominfo publish confirmation -- Minggu 3
5. Dashboard OPD per dinas -- Minggu 3
6. Eksekutif dashboard (bupati, dll.) -- Minggu 4

### Fase 3: Modul OPD (Prio Sedang)
1. DataKesehatanBulanan & DataPendidikanBulanan -> production -- Minggu 5-6
2. Modul DPUPR (Jalan, Jembatan, Drainase) -- Minggu 7-8
3. Modul DINSOS (PMKS, Bansos, Disabilitas) -- Minggu 7-8
4. Modul DUKCAPIL (KTP, Akta) -- Minggu 9-10
5. Modul DLH, DISTAN, remaining OPD -- Minggu 11-12

### Fase 4: Portal Publik & API (Prio Rendang)
1. Landing page dengan branding Sangihe -- Minggu 12
2. Public statistics page -- Minggu 13
3. Public API v1 -- Minggu 14
4. SIPDT Nasional gateway -- Minggu 15+

---

## 15. KEBIJAKAN PENGHAPUSAN FILE

### 15.1 File Dokumentasi yang Dihapus

| File | Alasan |
|------|--------|
| GOVERNMENT_DIGITAL_PLATFORM_ARCHITECTURE.md | Isi sudah ada di ARCHITECTURE.md |
| DASHBOARD_DOCUMENTATION.md | Info technical detail sudah obsolet |
| IMPLEMENTATION_CHECKLIST.md | Roadmap sudah di Fase 1 (selesai) |
| IMPLEMENTATION_REPORT.md | Status sudah tercermin di git history |
| EXPORT_ROUTES_SETUP.md | Informasi setup sudah di README.md |
| PETA_PINTAR_ENHANCEMENT_GUIDE.md | Fitur peta sudah ada di core |
| QUICK_REFERENCE_DASHBOARD.md | Duplikat informasi |
| QUICK_START.md | Duplikat informasi README.md |
| README.md | Akan digantikan oleh README.md baru |
| AGENTS.md | File kosong |

### 15.2 File yang Dipertahankan

| File | Alasan |
|------|--------|
| ARCHITECTURE.md | Dokumen arsitektur utama |
| TODO.md | Tracking progress implementasi |
| docs/SIPANDU-ARCHITECTURE.md | Arsitektur konseptual ringkas |
| docs/SIPDT_ENTERPRISE_ARCHITECTURE_BLUEPRINT.md | Blueprint enterprise |
| docs/PORTAL_PUBLIK_UIUX_SPEC.md | UI/UX spec portal publik |

### 15.3 File Teknis yang Dihapus

| File | Alasan |
|------|--------|
| app/Filament/Pages/PetaDesa.php | Duplikat dari PetaPintar.php |

---

## LAMPIRAN: CONTOH NOMOR SUBMISSION

SB-KESEHATAN-KEC-3-B08-T2026-001

SB        = Submission Batch
KESEHATAN = Modul
KEC       = Prefix Kecamatan
3         = ID Kecamatan
B08       = Bulan 08
T2026     = Tahun 2026
001       = Sequence number

---

Document: ARCHITECTURE.md -- SIPANDU Platform
Maintainer: Tim Developer
Last Updated: 8 Agustus 2026
