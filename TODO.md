# TODO - SIPANDU Platform Implementation

## Arsitektur Dokumen

- `ARCHITECTURE.md` — Dokumen arsitektur utama (platform overview)
- `PUSKESMAS_SEKOLAH_DESIGN.md` — Desain sistem Admin Puskesmas & Admin Sekolah Kecamatan
- `TODO.md` — Tracking progress implementasi (file ini)
- `docs/SIPANDU-ARCHITECTURE.md` — Arsitektur konseptual ringkas
- `docs/SIPDT_ENTERPRISE_ARCHITECTURE_BLUEPRINT.md` — Blueprint enterprise
- `docs/PORTAL_PUBLIK_UIUX_SPEC.md` — UI/UX spec portal publik
- `docs/PRODUCTION_ARCHITECTURE.md` — Arsitektur production-ready (21 sections)
- `docs/TECHNICAL_AUDIT_REPORT.md` — Audit teknis SIPANDU (8 Agustus 2026)
- **`docs/IMPLEMENTATION_BLUEPRINT.md`** — **Blueprint final: audit + solusi + spec perbaikan**

---

## 🔴 AUDIT FINDINGS — PRIORITAS TINGGI

> Hasil deep technical audit source code — 8 Agustus 2026
> Full report: `docs/TECHNICAL_AUDIT_REPORT.md`

### CRITICAL — Fix Sekarang

| # | Item | File | Effort | Status |
|---|------|------|--------|--------|
| A1 | Fix `forward_to_kominfo` notification dead code | `app/Services/BatchApprovalService.php:105-132` | < 30 menit | **✅ DONE** |
| A2 | Add unique validation on `penduduk.nik` | `app/Http/Controllers/Api/MobileSyncController.php:146-160` | < 1 jam | **✅ DONE** |

### HIGH — Fix Phase 2

| # | Item | Effort | Status |
|---|------|--------|--------|
| A3 | Add `submission_batch_id` FK to OPD tables | Migration + Model + Service | **✅ DONE** |
| A4 | Integrate kesehatan/pendidikan into batch workflow | Done via A3 | **✅ DONE** |
| A5 | Add test: notification dispatch | `tests/Feature/SubmissionBatchNotificationTest.php` | **✅ DONE** (7 tests) |
| A6 | Add test: NIK uniqueness | `tests/Feature/MobileSyncNikUniquenessTest.php` | **✅ DONE** (4 tests) |

### MEDIUM — Fix Phase 3

| # | Item | Effort | Status |
|---|------|--------|--------|
| A7 | Optimize `activeBatchForModule()` (WHERE in SQL) | `app/Services/SubmissionBatchService.php` | < 1 jam | **✅ DONE** |
| A8 | Consolidate scope visibility (3→1 pattern) | `app/Support/UserScope.php` + all models | 2-3 jam | **✅ DONE** |
| A9 | Consolidate role constants (3→1 location) | `app/Enums/AppRole.php` | 1-2 jam | **✅ DONE** |
| A10 | Add AdminDesa notification on rejection | 1 jam | **✅ DONE** |
| A11 | Add mobile sync rate limiting | 30 menit | **✅ DONE** |
| A12 | Remove duplicate `role_label` key | `app/Http/Controllers/Api/MobileAuthController.php` | < 15 menit | **✅ DONE** |

### LOW — Fix Phase 4

| # | Item | Effort | Status |
|---|------|--------|--------|
| A13 | Add OPD policies | `app/Policies/DataKesehatanBulananPolicy.php`, `DataPendidikanBulananPolicy.php`, `SubmissionBatchPolicy.php` | 1 jam | **✅ DONE** |
| A14 | Add composite index for public map | `database/migrations/2026_08_09_000001_add_composite_indexes_for_public_map_and_scope.php` | 30 menit | **✅ DONE** |
| A15 | Verify submission_batch_items unique constraint | `database/migrations/2026_07_10_000010_create_submission_batches_and_items_table.php:40` — confirmed `unique(['submission_batch_id', 'entity_type', 'entity_id'])` | 15 menit | **✅ DONE** |

---

## PRIORITAS IMPLEMENTASI

### FASE 1: Fondasi Platform ✅ SELESAI
- [x] RBAC dengan Spatie Permission
- [x] Master wilayah (Kabupaten, Kecamatan, Desa)
- [x] Data Rumah, Keluarga, Penduduk
- [x] SubmissionBatch workflow
- [x] Batch approval service
- [x] Notifikasi database
- [x] Audit trail
- [x] Mobile app (capture rumah/kk/penduduk)
- [x] Model: DataKesehatanBulanan, DataPendidikanBulanan
- [x] Model: IbuHamil, Bayi, TenagaKesehatan, PelayananKesehatan, KasusPenyakit

### FASE 2: Desain Baru ✅ SELESAI
- [x] `PUSKESMAS_SEKOLAH_DESIGN.md` — Desain lengkap sistem Admin Puskesmas
- [x] Desain: 7 modul input data kesehatan puskes
- [x] Desain: 5 modul input data sekolah kecamatan
- [x] Desain: Penduduk Search auto-fill component
- [x] Desain: Master data FasilitasKesehatan (Puskesmas)
- [x] Desain: Master data Sekolah
- [x] Desain: Role baru admin_puskes_[id], admin_sekolah_kec_[id]

### FASE 3: Penyempurnaan Workflow (Prio Tinggi)
- [ ] Cross-OPD verification routing
- [ ] OPD batch submission (DINKES, DISDIK)
- [ ] Kepala Dinas approval flow
- [ ] Kominfo publish confirmation
- [ ] Dashboard OPD per dinas
- [ ] Eksekutif dashboard (bupati, wakil bupati, kepala dinas)

### FASE 4: Modul OPD (Prio Sedang)
- [ ] Modul DINKES: FasilitasKesehatanResource + StokObat + RekamMedis
- [ ] Modul DISDIK: SekolahResource + DataGuru + DataSiswa + DataPrasarana
- [ ] Modul DPUPR (Jalan, Jembatan, Drainase)
- [ ] Modul DINSOS (PMKS, Bansos, Disabilitas)
- [ ] Modul DUKCAPIL (KTP, Akta)
- [ ] Modul DLH, DISTAN, remaining OPD

### FASE 5: Portal Publik & API (Prio Rendang)
- [ ] Landing page dengan branding Sangihe
- [ ] Public statistics page
- [ ] Public API v1
- [ ] SIPDT Nasional gateway

---

## MODUL DATA OPD - DETAIL

### Dinas Kesehatan (DINKES) — Status: ⚠️ Fondasi Ada, TAPI BYPASS Workflow Approval

> **AUDIT FINDING**: `data_kesehatan_bulanans` tidak punya `submission_batch_id` FK.
> Semua data kesehatan langsung live TANPA melalui batch approval workflow.
> Ini adalah **CRITICAL issue** — perlu diperbaiki di A3 & A4.

| # | Modul | Deskripsi | Status |
|---|-------|---------|--------|
| 1 | DataKesehatanBulanan | Master data + resource ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 2 | IbuHamil | Model & resource ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 3 | Bayi | Model & resource ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 4 | TenagaKesehatan | Model & resource ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 5 | PelayananKesehatan | Model & resource ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 6 | KasusPenyakit | Model & resource ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 7 | **FasilitasKesehatan** | Master puskes, pustu, posyandu | ⬜ Belum |
| 8 | **StokObat** | Inventory obat di puskes | ⬜ Belum |
| 9 | **RekamMedis** | ICD-10 per kunjungan | ⬜ Belum |
| 10 | DataImunisasi | Bulanan | ⬜ Belum |
| 11 | DataObatAlkes | Bulanan | ⬜ Belum |
| 12 | DataKB | Bulanan | ⬜ Belum |

### Dinas Pendidikan (DISDIK) — Status: ⚠️ Fondasi Ada, TAPI BYPASS Workflow Approval

> **AUDIT FINDING**: `data_pendidikan_bulanans` tidak punya `submission_batch_id` FK.
> Semua data pendidikan langsung live TANPA melalui batch approval workflow.
> Ini adalah **CRITICAL issue** — perlu diperbaiki di A3 & A4.

| # | Modul | Deskripsi | Status |
|---|-------|---------|--------|
| 1 | DataPendidikanBulanan | Master data ada | ⚠️ Fondasi ada, **WORKFLOW BROKEN** |
| 2 | **Sekolah** | Master data sekolah | ⬜ Belum |
| 3 | **DataGuru** | Detail guru (relasi penduduk) | ⬜ Belum |
| 4 | **DataSiswa** | Detail siswa (relasi penduduk) | ⬜ Belum |
| 5 | **DataPrasaranaSekolah** | Prasarana & sarana | ⬜ Belum |
| 6 | DataKelulusan | Semester | ⬜ Belum |
| 7 | DataGTK | Tahunan | ⬜ Belum |
| 8 | DataBeasiswa | Tahunan | ⬜ Belum |

### OPD Lainnya — Status: Belum

| OPD | Modul | Status |
|-----|-------|--------|
| DPUPR | DataJalan, DataJembatan, DataDrainase | ⬜ |
| DINSOS | DataPMKS, DataBansos, DataDisabilitas | ⬜ |
| DUKCAPIL | DataKTP, DataAkta | ⬜ |
| DLH | DataSampah, DataKualitasLingkungan | ⬜ |
| DISTAN | DataLahan, DataPeternakan, DataNelayan | ⬜ |
| DISPERINDAG | DataIndustri, DataUMKM, DataHargaPokok | ⬜ |
| BKPSDMD | DataPNS, DataHonorer | ⬜ |
| BAPPEDA | DataRPJMD, DataProyek | ⬜ |
| DISHUB | DataKendaraan, DataJalanRay | ⬜ |
| BPBD | DataBencana, DataPosko | ⬜ |

---

## WORKFLOW ENHANCEMENT

### Existing — Sudah Berfungsi ✅
- [x] SubmissionBatchService (rumah, keluarga, penduduk, mixed)
- [x] BatchApprovalService (draft, pending, in_review, approved, rejected, revision, archived)
- [x] Payload snapshot versioning
- [x] Immutable audit_trails
- [x] Idempotent publish
- [x] Notifikasi database (BatchNotification)
- [x] Soft deletes everywhere

### Workflow Fixes (dari Audit) — DONE ✅

- [x] **FIX A1**: Perbaiki `forward_to_kominfo` notification dead code
- [x] **FIX A2**: Tambahkan validasi NIK uniqueness di mobile sync
- [x] **FIX A10**: Tambahkan notification untuk AdminDesa saat batch ditolak
- [x] **FIX A12**: Hapus duplicate `role_label` key di mobile profile API (kesehatan, pendidikan)

### Workflow Enhancements Baru
- [ ] Cross-OPD verification routing
- [ ] OPD batch submission (DINKES, DISDIK) — **CRITICAL: currently bypasses workflow**
- [ ] Kepala Dinas approval flow
- [ ] Kominfo publish confirmation
- [ ] Dashboard OPD per dinas
- [ ] Eksekutif dashboard (bupati, wakil bupati, kepala dinas)
- [ ] Update SubmissionBatchService (add module puskes & sekolah)
- [ ] Update BatchApprovalService (add puskes & sekolah workflow)
- [ ] Update PanelAccess (add isAdminPuskes, isAdminSekolahKecamatan, canAccessPuskes, canAccessSekolah)
- [ ] Update User model (add puskes_id, sekolah_id)
- [ ] Page: KirimBatchPuskes ke Kecamatan
- [ ] Page: KirimBatchSekolah ke Kecamatan
- [ ] Component: PendudukSearch (autocomplete auto-fill)

---

## ANALISIS KAPASITAS LARAVEL

### Apakah Laravel Mampu? — YA

| Aspek | Kapasitas | Status |
|-------|-----------|--------|
| Database | MySQL/PostgreSQL | ✅ Ready |
| Concurrent Users | 100-500 | ✅ Ready |
| Data Volume | Millions | ✅ Ready with optimization |
| API | REST/GraphQL | ✅ Ready |
| Caching | Redis/Memcached | ✅ Ready |
| Queue | Redis/SQS | ✅ Ready |

### Rekomendasi untuk Production
1. Gunakan PostgreSQL untuk better JSON support
2. Implementasikan Redis caching
3. Gunakan Laravel Horizon untuk queue management
4. Gunakan Laravel Octane untuk performance
5. Setup load balancer untuk horizontal scaling

---

## Implementation Roadmap (dari Blueprint)

### Sprint 1: Critical Fixes (Week 1)
| Day | Task | Spec | Status |
|-----|------|------|--------|
| 1-2 | Fix notification dead code | SPEC-001 | ✅ DONE |
| 3-4 | Fix NIK uniqueness | SPEC-002 | ✅ DONE |
| 5 | Fix mobile duplicate role_label | SPEC-007a | ✅ DONE |

### Sprint 2: OPD Integration (Week 2)
| Day | Task | Spec | Status |
|-----|------|------|--------|
| 1 | Migration + Model update (FK) | SPEC-003 | ✅ DONE |
| 2 | Service + Filament update | SPEC-003 | ✅ DONE |
| 3 | Write OPD integration tests | `tests/Feature/SubmissionBatchOpdIntegrationTest.php` | ✅ DONE (6 tests) |
| 4 | Write notification tests | SPEC-001 | ✅ DONE |
| 5 | Write NIK uniqueness tests | SPEC-002 | ✅ DONE |

### Sprint 3: Refactoring (Week 4)
| Day | Task | Spec | Status |
|-----|------|------|--------|
| 1-2 | Scope consolidation | SPEC-004 | ✅ DONE |
| 3 | Role enum | SPEC-005 | ✅ DONE |
| 4 | Batch optimization | SPEC-006 | ✅ DONE |
| 5 | Rate limiting + AdminDesa notification | SPEC-007, SPEC-008 | ✅ DONE |

### Sprint 4: Hardening & Polish (Week 5)
| Day | Task | Status |
|-----|------|--------|
| 1 | OPD policies (Rumah, Kesehatan, Pendidikan, Submission) | ✅ DONE |
| 2 | Composite indexes for public map + scope | ✅ DONE |
| 3 | Verify submission_batch_items unique constraint | ✅ DONE |
| 4-5 | Code review + documentation update | TODO |

### Sprint 4: Testing & Polish (Week 5)
| Day | Task |
|-----|------|
| 1-3 | Write all missing tests |
| 4 | Integration testing |
| 5 | Code review + documentation update |

---

## CATATAN PENTING

### Admin Sekolah Kecamatan
1 admin mengelola SEMUA sekolah di 1 kecamatan (bukan 1 admin per sekolah).
Contoh: Admin Sekolah Kec. Tahuna = input data ±25 sekolah di Tahuna.

### Admin Puskesmas
1 admin per puskes, dengan 7 modul input per bulan:
Pelayanan, Ibu Hamil, Bayi, Nakes, Stok Obat, Penyakit, Kumulatif.

### Prinsip Auto-fill Penduduk
Setiap input data kesehatan WAJIB memilih pasien dari tabel penduduk.
Ini memastikan data konsisten, trackable, dan analisis demografi.

---

## Referensi Dokumen

| Dokumen | Fungsi |
|---------|--------|
| `ARCHITECTURE.md` | Overview arsitektur platform |
| `PUSKESMAS_SEKOLAH_DESIGN.md` | Desain Admin Puskesmas & Sekolah |
| `docs/PRODUCTION_ARCHITECTURE.md` | Arsitektur production-ready (21 sections) |
| `docs/TECHNICAL_AUDIT_REPORT.md` | Laporan audit teknis (17 sections) |
| `docs/IMPLEMENTATION_BLUEPRINT.md` | **Blueprint final: audit + solusi + 8 SPEC perbaikan** |

---

*Last Updated: 9 Agustus 2026*
*Version: 3.3 — Sprint 3 Refactoring + Sprint 4 Hardening Complete*
*Blueprint Reference: docs/IMPLEMENTATION_BLUEPRINT.md*
*Aktifitas: All 15 audit findings DONE. Sprint 4 review remaining.*
