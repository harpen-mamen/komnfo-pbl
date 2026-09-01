# ARSITEKTUR SATU DATA KABUPATEN KEPULAUAN SANGIHE
## Sistem Pemerintahan Digital Terpadu

- Versi: 1.0 | Tanggal: 9 Agustus 2026
- Referensi: Perpres No. 39/2019 tentang Satu Data Indonesia
- Platform: Laravel 10 + Filament + Flutter

---

## 1. VISI & MISI

### 1.1 Visi
> "Terwujudnya tata kelola data pemerintahan Kabupaten Kepulauan Sangihe yang terpadu, akurat, mutakhir, dapat dipertanggungjawabkan, dan mudah diakses sebagai dasar perencanaan, pelaksanaan, evaluasi, dan pengendalian pembangunan."

### 1.2 Misi
1. **Standarisasi Data**: Metadata, kode referensi, dan format data sesuai standar nasional
2. **Integrasi Lintas OPD**: Menghilangkan silo data antar dinas
3. **Kualitas Data**: Validasi otomatis, cross-verification, dan audit trail
4. **Aksesibilitas**: Portal publik, API terbuka, dan mobile app
5. **Pemerintahan Digital**: Layanan publik yang efisien, transparan, dan akuntabel

---

## 2. TUJUH PRINSIP SATU DATA

| No | Prinsip | Implementasi |
|----|---------|--------------|
| 1 | **Standar Data** | Master referensi bersama (kode wilayah, KBLI, ICD-10, dll) |
| 2 | **Metadata** | Setiap dataset punya metadata terstandar |
| 3 | **Kode Referensi** | Kode wilayah, kode OPD, klasifikasi sesuai BSSN/BPS |
| 4 | **Interoperabilitas** | API publik, format JSON/XML sesuai SIPDT Nasional |
| 5 | **Akuntabilitas** | Walidata, PJ PD, audit trail, version history |
| 6 | **Keakuratan** | Validasi real-time, cross-verification, conflict resolution |
| 7 | **Keterkinian** | Update berkala, tracking perubahan, masa aktif data |

---

## 3. ARSITEKTUR GOVERNANCE (TATA KELOLA)

### 3.1 Struktur Organisasi Satu Data

```
WALIKADATA KABUPATEN (Kepala Dinas Kominfo)
  ¦
  +-- KOORDINATOR DATA STATISTIK (Bidang Persandian & Statistik)
  ¦     • Mengelola metadata seluruh dataset
  ¦     • Memvalidasi standar data
  ¦     • Menerbitkan kode referensi
  ¦
  +-- PJ DATA PRODUSEN (Perangkat Daerah)
  ¦     • DINKES  ? PJ: Kabid Data dan Informasi
  ¦     • DISDIK  ? PJ: Kabid Perencanaan
  ¦     • DPUPR   ? PJ: Kabid Bina Marga
  ¦     • ... (20 OPD)
  ¦
  +-- PJ DATA KECAMATAN (15 Kecamatan)
  ¦     • Validasi data wilayah
  ¦     • Agregasi data desa
  ¦
  +-- PJ DATA DESA (197 Desa)
        • Input data di mobile app
        • Verifikasi lapangan
```

### 3.2 Peran Baru dalam Sistem

| Role | Tugas | Hak Akses |
|------|-------|-----------|
| **Wali Data** | Menetapkan standar data & metadata | Validasi metadata, kelola master referensi |
| **Koordinator Statistik** | Validasi klasifikasi & kode | Kelola KBLI, ICD-10, dll |
| **Produsen Data** (existing) | Input data sesuai domain | Submit batch ke sistem |
| **Validator** | Verifikasi silang | Approve/reject batch |
| **Pengguna Data** | Akses data approved | Read-only API |

---

## 4. ARSITEKTUR TEKNIS 5-LAYER

### 4.1 Layer Overview

```
+-------------------------------------------------------------+
¦ LAYER 5: PRESENTATION (Web Admin + Mobile + Portal Publik) ¦
+-------------------------------------------------------------¦
¦ LAYER 4: APPLICATION SERVICES (Filament Pages, Controllers)¦
+-------------------------------------------------------------¦
¦ LAYER 3: DOMAIN SERVICES (Use Cases / Business Logic)      ¦
¦   +-- Submission/Workflow/Notification/Aggregation         ¦
+-------------------------------------------------------------¦
¦ LAYER 2: DOMAIN MODELS (Aggregates, Entities, Value Objects)¦
¦   +-- Domain/Identity/Domain/Wilayah/Domain/Sectoral       ¦
+-------------------------------------------------------------¦
¦ LAYER 1: INFRASTRUCTURE (DB, Cache, Queue, External API)   ¦
¦   +-- Repositories, Gateways, Adapters                     ¦
+-------------------------------------------------------------+
```

### 4.2 Domain Contexts (Bounded Contexts)

```
app/Contexts/
+-- Identity/             # User, Role, Registrasi, Otorisasi
¦   +-- Models/           # User, UserRegistration
¦   +-- Services/         # UserRegistrationService, AuthService
¦   +-- Policies/         # UserPolicy, RolePolicy
¦   +-- Events/           # UserRegistered, UserApproved
¦
+-- Wilayah/              # Master wilayah & data spasial
¦   +-- Models/           # Kabupaten, Kecamatan, Desa, Dusun
¦   +-- Services/         # WilayahService, KodeReferensiService
¦   +-- Repositories/     # WilayahRepository
¦
+-- MasterData/           # Kode referensi bersama
¦   +-- Models/           # KodeReferensi, StandarData, Metadata
¦   +-- Services/         # StandarDataService, KodeReferensiService
¦   +-- Seeders/          # Initial master reference data
¦
+-- Penduduk/             # Core: Rumah, Keluarga, Penduduk
¦   +-- Models/           # Rumah, Keluarga, Penduduk
¦   +-- Services/         # PendudukImportService
¦   +-- Repositories/     # PendudukRepository
¦
+-- Sectoral/             # Per-OPD data (bounded per module)
¦   +-- Kesehatan/        # Dinkes: data kesehatan, puskesmas, dll
¦   +-- Pendidikan/       # Disdik: data pendidikan, sekolah, dll
¦   +-- PekerjaanUmum/    # DPUPR: jalan, jembatan, irigasi
¦   +-- Sosial/           # Dinsos: PMKS, bansos
¦   +-- Kependudukan/     # Dukcapil: KTP, akta
¦   +-- Lingkungan/       # DLH: sampah, kualitas
¦   +-- Pertanian/        # Distan: lahan, peternakan
¦   +-- Perdagangan/      # Disperindag: UMKM, pasar
¦   +-- Perhubungan/      # Dishub: kendaraan, jalan
¦   +-- Bencana/          # BPBD: kejadian bencana
¦   +-- ... (10+ contexts)
¦
+-- Submission/           # SubmissionBatch workflow
¦   +-- Models/           # SubmissionBatch, SubmissionBatchItem
¦   +-- Services/         # SubmissionCreator, SubmissionSubmitter, SubmissionApprover
¦   +-- Events/           # BatchSubmitted, BatchApproved, etc.
¦
+-- Workflow/             # State machine & verification
¦   +-- Models/           # WorkflowInstance, WorkflowHistory
¦   +-- Services/         # WorkflowEngine, CrossOpdVerificationService
¦   +-- StateMachine/     # States, Transitions
¦
+-- Notification/         # Multi-channel notifications
¦   +-- Channels/         # Database, Email, Push, SMS
¦   +-- Services/         # NotificationDispatcher
¦   +-- Templates/        # Email templates, Push templates
¦
+-- Aggregation/          # Dashboard, analytics, statistics
¦   +-- Services/         # DashboardAggregator, StatisticsService
¦   +-- Queries/          # Optimized aggregation queries
¦   +-- Cache/            # Caching strategies
¦
+-- Publication/          # Portal publik & API
¦   +-- Public/           # PublicPortalService
¦   +-- Api/              # PublicApiService (JSON API)
¦   +-- Gateway/          # SIPDTNasionalGateway (forward to pusat)
¦
+-- Audit/                # Audit trail, activity log
¦   +-- Services/         # AuditService
¦   +-- Events/           # AuditableEvents
¦
+-- Shared/               # Cross-cutting concerns
    +-- Enums/            # AppRole, StatusBatch, dll
    +-- Support/          # UserScope, PanelAccess
    +-- Concerns/         # HasOpdModule, HasSubmissionBatchApproval
    +-- ValueObjects/     # Coordinates, Periode, Klasifikasi
```

---

## 5. ARSITEKTUR DATA — SATU DATA INDONESIA

### 5.1 Standar Metadata

Setiap **dataset** yang ada di sistem harus memiliki metadata sesuai standar Satu Data Indonesia:

```
+--------------------------------------------------------------+
¦ METADATA STRUCTURE (Sesuai Perpres 39/2019)                   ¦
+--------------------------------------------------------------¦
¦ 1. ID Dataset                                                  ¦
¦ 2. Judul Dataset                                               ¦
¦ 3. Deskripsi Dataset                                           ¦
¦ 4. Produsen Data (OPD)                                         ¦
¦ 5. Penanggung Jawab                                            ¦
¦ 6. Kontak Produsen                                             ¦
¦ 7. Periode Update                                              ¦
¦ 8. Cakupan Wilayah (Kabupaten/Kecamatan/Desa)                  ¦
¦ 9. Bahasa (Indonesia/Inggris)                                  ¦
¦ 10. Format Data (JSON/CSV/XLSX/API)                            ¦
¦ 11. Frekuensi Update (Bulanan/Semester/Tahunan/Real-time)      ¦
¦ 12. Status Data (Aktif/Arsip/Eksperimental)                    ¦
¦ 13. Lisensi (CC BY 4.0 / Open Data / Terbatas)                 ¦
¦ 14. Sumber Data (Survei/Administrasi/Sensor/API)               ¦
¦ 15. Standar Klasifikasi (KBLI/ICD-10/Kodefikasi Wilayah)      ¦
¦ 16. Tags / Kata Kunci                                          ¦
¦ 17. URL Akses                                                  ¦
¦ 18. Tanggal Dibuat / Diperbarui                                ¦
+--------------------------------------------------------------+
```

### 5.2 Tabel Standar Data (Master Referensi)

```
kode_referensi
  +-- jenis (kode_wilayah, kbli, icd10, icd9, kbji, sni, dll)
  +-- kode
  +-- nama
  +-- deskripsi
  +-- parent_id (untuk hierarki)
  +-- standar_sumber (BPS/BSSN/Kemenkes/UNESCO)
  +-- aktif
  +-- version

standar_metadata
  +-- dataset_id
  +-- judul
  +-- deskripsi
  +-- produsen_id
  +-- kontak
  +-- periode_update
  +-- cakupan_wilayah
  +-- format
  +-- frekuensi
  +-- status_aktif
  +-- lisensi
  +-- klasifikasi_standar
  +-- tags
  +-- url
  +-- timestamps
```

### 5.3 Alur Satu Data End-to-End

```
PRODUSEN DATA ? WALIDATA ? PORTAL PUBLIK

1. PRODUSEN (OPD/Kecamatan/Desa)
   • Input data via web/mobile
   • Submit batch ke sistem
   • Status: DRAFT ? PENDING

2. CROSS-VERIFICATION (Multi-OPD Validator)
   • DUKCAPIL verifikasi NIK/akta
   • DISDIK verifikasi NPSN
   • DINKES verifikasi faskes
   • Status: PENDING ? CROSS_VERIFICATION

3. WALIDATA (Kominfo)
   • Validasi metadata
   • Cek standar klasifikasi
   • Approve batch ? PUBLISHED
   • Generate metadata otomatis

4. PORTAL PUBLIK & API
   • Data tersedia di https://data.sangihekab.go.id
   • API: GET /api/v1/public/{dataset}
   • Format: JSON, CSV, XLSX
   • Lisensi: CC BY 4.0

5. SIPDT NASIONAL
   • Push otomatis ke SIPDT Gateway
   • Sinkronisasi dengan data nasional
   • Dashboard nasional
```

---

## 6. ARSITEKTUR PEMERINTAHAN DIGITAL

### 6.1 Layanan Publik Digital

| Layanan | Pengguna | Sistem |
|---------|----------|--------|
| **Surat Domisili Online** | Masyarakat | Layanan publik module |
| **Pendaftaran Penduduk** | RT/RW, Desa | Mobile app ? Sinkron |
| **Pengaduan Masyarakat** | Masyarakat | Form publik + tracking |
| **Informasi Statistik** | Masyarakat, peneliti | Portal publik + API |
| **Transparansi Anggaran** | Masyarakat, LSM | Dashboard publik |
| **e-Procurement** | OPD, vendor | Modul terpisah |
| **e-Office (Surat Menyurat)** | Seluruh ASN | Modul terpisah |

### 6.2 Dashboard untuk Pimpinan

```
+-------------------------------------------------------------+
¦ BUPATI / WAKIL BUPATI DASHBOARD                             ¦
+-------------------------------------------------------------¦
¦                                                              ¦
¦ [KPI Cards] Total Penduduk | Total KK | Total Rumah          ¦
¦             APBD Realisasi | Indeks Pembangunan Manusia      ¦
¦                                                              ¦
¦ [Map] Sebaran penduduk, sekolah, puskesmas per kecamatan     ¦
¦                                                              ¦
¦ [Charts] Pertumbuhan penduduk | Demografi | IPM              ¦
¦                                                              ¦
¦ [Alerts] Data belum lengkap | Batch pending | Deadline dekat ¦
¦                                                              ¦
¦ [Quick Links] Lihat data | Approve batch | Statistik         ¦
+-------------------------------------------------------------+
```

---

## 7. ARSITEKTUR INTEGRASI

### 7.1 Integrasi Internal (Antar Sistem)

```
SIPP (Sistem Pemerintahan Kabupaten)
  +-- SIPANDU (Data Terpadu) ? kita di sini
  +-- SIMDA (Keuangan)
  +-- SIMPEG (Kepegawaian)
  +-- e-Procurement
  +-- e-Office
  +-- SIPD (Perencanaan)
```

### 7.2 Integrasi Eksternal

```
SANGIHE ?? SIPDT NASIONAL (data.go.id)
         ?? KEMENDAGRI (data wilayah)
         ?? BPS (data statistik)
         ?? KEMENKES (data kesehatan)
         ?? BANK INDONESIA (data ekonomi)
         ?? PROVINSI SULUT
```

### 7.3 API Gateway

```
https://api.sangihekab.go.id/v1/
  +-- /public/datasets          # List dataset
  +-- /public/datasets/{id}      # Get specific dataset
  +-- /public/stats/{indicator}  # Statistical indicators
  +-- /mobile/sync/*            # Mobile app sync
  +-- /internal/auth/*          # Internal auth
  +-- /webhooks/*               # Webhook receiver
```

---

## 8. KEAMANAN & COMPLIANCE

### 8.1 Compliance Requirements

| Regulasi | Compliance |
|----------|-----------|
| UU No. 14/2008 (Keterbukaan Informasi) | ? Portal publik |
| UU No. 25/2009 (Pelayanan Publik) | ? Standar layanan |
| UU No. 30/2014 (Administrasi Pemerintahan) | ? Audit trail |
| Perpres 39/2019 (Satu Data Indonesia) | ? Standar metadata |
| PP 71/2019 (Sistem Pemerintahan Berbasis Elektronik) | ? Integrasi |
| UU No. 27/2022 (Pelindungan Data Pribadi) | ? PDP compliance |

### 8.2 PDP (Pelindungan Data Pribadi) Compliance

```
DATA PRIBADI SENSITIF (perlu proteksi khusus):
  • NIK (16 digit) ? Mask: 3201****0001
  • Nama lengkap ? Bisa ditampilkan utuh untuk verifikasi
  • Alamat lengkap ? Tampil kecamatan & desa saja
  • Tanggal lahir ? Tampil tahun saja untuk agregasi
  • Foto ? Watermark + resize untuk publik
  • Nomor HP ? Enkripsi di DB, hanya tampil 4 digit terakhir
  • Pekerjaan spesifik ? Agregasi saja
  • Penghasilan ? Agregasi dengan kategori
  • Riwayat kesehatan ? TIDAK BOLEH dipublikasikan
  • Status disabilitas ? Tidak untuk publik tanpa izin
```

### 8.3 Audit & Compliance

```
- Setiap akses data dicatat di audit_trails
- Setiap perubahan data punya diff history
- Setiap submit batch wajib ada approval berjenjang
- Retensi data minimum 5 tahun (UU 14/2008)
- Backup harian + off-site backup
- Penetration testing setiap 6 bulan
- ISO 27001 compliance target (2027)
```

---

## 9. ARSITEKTUR TECHNICAL DEBT YANG HARUS DIATASI

### 9.1 Service Layer Decomposition
[Lihat Sprint 5 implementation]

### 9.2 Domain Modeling
[Lihat Sprint 6 implementation]

### 9.3 Cross-OPD Verification
[Lihat Sprint 7 implementation]

### 9.4 Public API v1
[Lihat Sprint 8 implementation]

### 9.5 Core Workflow Testing
[Lihat Sprint 9 implementation]

---

## 10. ROADMAP IMPLEMENTASI

### Phase 1: SATU DATA FOUNDATION (Sprint 5-6)
- [x] Sprint 5: Service Layer Decomposition
- [x] Sprint 6: Domain Modeling + Master Referensi

### Phase 2: WORKFLOW MATURITY (Sprint 7-8)
- [x] Sprint 7: Cross-OPD Verification
- [x] Sprint 8: Public API + Portal Publik

### Phase 3: PEMERINTAHAN DIGITAL (Sprint 9-11)
- [x] Sprint 9: Core Workflow Testing
- [x] Sprint 10: e-Office & Layanan Publik
- [x] Sprint 11: Dashboard Eksekutif

### Phase 4: INTEGRASI & SCALE (Sprint 12-14)
- [x] Sprint 12: SIPDT Nasional Gateway
- [x] Sprint 13: Performance & Caching
- [x] Sprint 14: ISO 27001 Compliance

---

## 11. KRITERIA KEBERHASILAN "SATU DATA"

| Kriteria | Target |
|----------|--------|
| 100% dataset ber-metadata | Sprint 6 |
| 100% data wilayah approved dalam 30 hari | Sprint 7 |
| 95% uptime portal publik | Sprint 8 |
| <2 detik response time dashboard | Sprint 13 |
| 100% compliance UU PDP | Sprint 14 |
| API response time <200ms (p95) | Sprint 13 |
| 100% audit trail completeness | Sprint 9 |
| 0% data silos antar OPD | Sprint 5-7 |

---

*Document: SATU_DATA_SANGIHE_ARCHITECTURE.md*
*Maintainer: Tim SIPANDU + Diskominfo Sangihe*
*Last Updated: 9 Agustus 2026*
