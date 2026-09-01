# LAPORAN AUDIT TEKNIS SIPANDU — AGUSTUS 2026

> **Fase**: FASE 1 Discovery ✅ | FASE 2 Architecture Audit ✅ | FASE 3 Risk Analysis ✅ | FASE 4 Decision ✅ | FASE 5-7: Implementation Pending
>
> **Auditor**: Claude Code (claude-opus-4-6)
>
> **Tanggal**: 8 Agustus 2026
>
> **Scope**: Full codebase audit — services, models, migrations, policies, tests, API controllers, Filament resources, routes, middleware, notifications, panel access logic, seeders

---

## A. Executive Summary

SIPANDU adalah aplikasi Laravel 10 + Filament 2.17 yang dibangun dengan arsitektur solid. Sistem workflow batch approval, audit trail, snapshot versioning, dan RBAC sudah terimplementasi dengan baik. Namun audit menemukan **5 masalah teknis yang perlu perbaikan**, 其中 **1 CRITICAL** (data integrity), **2 HIGH** (security & UX), dan **2 MEDIUM** (performance & consistency).

### Temuan Utama

| Severity | # | Finding | Lokasi |
|----------|---|---------|--------|
| 🔴 CRITICAL | 1 | Data kesehatan & pendidikan **BYPASS seluruh workflow approval** — tidak ada `submission_batch_id` FK, trait `HasSubmissionBatchApproval` tidak efektif | Model + Migration |
| 🔴 CRITICAL | 2 | Mobile sync memungkinkan **duplicate NIK** — tidak ada validasi uniqueness | `MobileSyncController::storePenduduk()` |
| 🟠 HIGH | 3 | `forward_to_kominfo` notification **tidak pernah dikirim** ke Kominfo — dead code path | `BatchApprovalService.php:107-108` |
| 🟡 MEDIUM | 4 | `activeBatchForModule()` membaca **semua record** lalu filter di PHP — seharusnya WHERE clause di SQL | `SubmissionBatchService.php:496-510` |
| 🟡 MEDIUM | 5 | Inconsistency scope visibility — `AdminKecamatan` vs `PanelAccess`, 3 model berbeda implementasinya | Multiple model files |

### Hal yang Sudah BAIK

| # | Aspek | Status |
|---|-------|--------|
| 1 | SSOT pattern dengan snapshot versioning | ✅ Solid |
| 2 | Immutable audit trail (`audit_trails` table) | ✅ Solid |
| 3 | State machine batch approval dengan role-based transition | ✅ Solid |
| 4 | Soft deletes everywhere | ✅ Solid |
| 5 | RBAC dengan Spatie Permission (38 roles) | ✅ Solid |
| 6 | Payload snapshot pada setiap submit | ✅ Solid |
| 7 | Idempotent publish | ✅ Solid |
| 8 | 7 comprehensive feature tests | ✅ Solid |
| 9 | Laravel Sanctum untuk mobile auth | ✅ Solid |
| 10 | Immutable `audit_trails` (timestamps=false, no UPDATED_AT) | ✅ Solid |

---

## B. Kondisi Arsitektur Saat Ini

### Stack Teknologi

```
┌────────────────────────────────────────────────────────────┐
│  PHP 8.1 + Laravel 10.x + Filament 2.17                   │
│  Spatie Laravel Permission 6.25 (RBAC)                     │
│  Laravel Sanctum 3.3 (mobile API auth)                    │
│  MySQL 8.x                                                │
│  CACHE_DRIVER=file | QUEUE_CONNECTION=sync                │
└────────────────────────────────────────────────────────────┘
```

### Arsitektur Aplikasi

```
┌──────────────────────────────────────────────────────────────────┐
│                        FILAMENT ADMIN PANELS                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │ Kominfo  │  │Kecamatan │  │  Desa    │  │  OPD     │        │
│  │  Panel   │  │  Panel   │  │  Panel   │  │  Panel   │        │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘        │
│       │              │             │             │               │
│       └──────────────┴─────────────┴─────────────┘               │
│                            │                                      │
│               KirimDataKeKominfo Page (Batch Submission)          │
└─────────────────────────────┼──────────────────────────────────────┘
                              │
┌─────────────────────────────▼──────────────────────────────────────┐
│                      SUBMISSION BATCH SERVICE                       │
│  submit() → Draft → Pending → InReview → Approved/Rejected/Arch   │
└──────┬──────────┬───────────────┬─────────────┬───────────────────┘
       │          │               │             │
       ▼          ▼               ▼             ▼
   ┌───────┐ ┌──────────┐ ┌────────────────┐ ┌──────────────┐
   │Rumah  │ │Keluarga  │ │  Penduduk      │ │Kesehatan     │
   │       │ │          │ │                │ │Pendidikan    │
   │  ✅   │ │   ✅     │ │     ✅         │ │    ❌        │
   └───────┘ └──────────┘ └────────────────┘ └──────────────┘
```

### User Roles (38 Total)

```
SUPER ADMIN (full access)
├── ADMIN (full access)
├── ADMIN_KOMINFO (kabupaten-level)
├── ADMIN_OPD (generic OPD)
├── ADMIN_KECAMATAN (kecamatan-level)
├── ADMIN_DESA (desa-level)
└── OPD-SPECIFIC (30 roles)
    ├── admin_dinkes / operator_dinkes
    ├── admin_dispend / operator_dispend
    ├── admin_dpu / operator_dpu
    ├── admin_dinsos / operator_dinsos
    ├── admin_disperindag / operator_disperindag
    ├── admin_dukcapil / operator_dukcapil
    ├── admin_dlh / operator_dlh
    ├── admin_distan / operator_distan
    ├── admin_dishub / operator_dishub
    ├── admin_distpar / operator_distpar
    ├── admin_dispora / operator_dispora
    ├── admin_bkpsdmd / operator_bkpsdmd
    ├── admin_bappeda / operator_bappeda
    ├── admin_bapenda / operator_bapenda
    ├── admin_bpbd / operator_bpbd
    ├── admin_diskominfo / operator_diskominfo
    ├── admin_dpmptsp / operator_dpmptsp
    └── admin_distaman / operator_distaman
```

---

## C. Workflow Aktual

### AdminDesa → AdminKecamatan → Kominfo

```
┌─────────────────────────────────────────────────────────────────┐
│ AdminDesa                                                       │
│  │                                                               │
│  │ submit()                                                      │
│  │   STATUS_DRAFT → STATUS_PENDING ✅                            │
│  │   notificationType='submit' → notifyKecamatanAboutDesaBatch() │
│  │     → AdminKecamatan RECEIVES notification ✅                 │
│  ▼                                                               │
│ AdminKecamatan                                                   │
│  │                                                               │
│  │ forwardToKominfo()                                            │
│  │   STATUS_IN_REVIEW → STATUS_IN_REVIEW                        │
│  │   notificationType='forward_to_kominfo'                      │
│  │     → OUTER if ($notificationType !== null) ✅               │
│  │     → INNER if ($notificationType === 'submit')  ❌         │
│  │        (forward_to_kominfo !== 'submit' → SKIP inner)        │
│  │     → Falls to: notifyKecamatan()  ❌                       │
│  │     → notifyKominfo() NEVER CALLED  ❌                       │
│  ▼                                                               │
│ Kominfo                                                          │
│  │                                                               │
│  │ APPROVES BATCH                                                │
│  │   notificationType='kecamatan' → notifyKecamatan() ✅        │
│  │   notifyKominfo() NOT CALLED (wrong type)  ⚠️               │
│  ▼                                                               │
│ Batch APPROVED → publish() → audit_trail records ✅              │
└─────────────────────────────────────────────────────────────────┘
```

### Batch State Machine

```
   ┌──────────────────────────────────────────────────────────┐
   │                                                          │
   │  ┌──────┐    submit()    ┌──────────┐                   │
   │  │ DRAFT│ ──────────────▶│  PENDING │                   │
   │  └──────┘                └────┬─────┘                   │
   │                                │                         │
   │  ┌──────────┐    forward()     │                         │
   │  │IN_REVIEW │◀────────────────┘                         │
   │  └────┬─────┘                                          │
   │       │                                                 │
   │       ├─── approve() ───────────────────────▶ APPROVED──┤
   │       │                                     (publish)   │
   │       │                                                 │
   │       ├─── reject() ───────────────┐                    │
   │       │                             ▼                    │
   │       │                       ┌──────────┐               │
   │       │                       │ REJECTED │               │
   │       │                       └────┬─────┘               │
   │       │                            │                     │
   │       │                   archive()│                     │
   │       │                            ▼                     │
   │       │                      ┌──────────┐                │
   │       │                      │ ARCHIVED│                │
   │       │                      └──────────┘                │
   │       │                                                 │
   │       └─── revise() ──────▶ ┌──────────┐               │
   │                             │REVISION │                │
   │                             └────┬─────┘               │
   │                                  │                     │
   └─────── submit() ─────────────────┘                     │
   (from revision back to pending)                           │
   │                                                         │
   │  REJECTED ──── archive() ────────────▶ ARCHIVED        │
   │  APPROVED ──── archive() ────────────▶ ARCHIVED         │
   │                                                         │
   └─────────────────────────────────────────────────────────┘
```

### Mobile Offline Sync Flow

```
Mobile App                          Server
    │                                 │
    │ POST /api/v1/mobile/sync/push   │
    │   { operations: [...] }          │
    │ ────────────────────────────────▶│
    │                                 │
    │   storeRumah()                   │
    │     → Rumah::create()   ❌      │
    │     (direct write, no batch)     │
    │                                 │
    │   storeKeluarga()               │
    │     → Keluarga::create() ❌     │
    │     (direct write, no batch)     │
    │                                 │
    │   storePenduduk()               │
    │     → Penduduk::create() ❌     │
    │     (NO NIK uniqueness check)    │
    │                                 │
    │   MobileSyncOperation::create() │
    │     (operation log only)         │
    │                                 │
    │◀───────────────────────────────│
    │   { results: [...] }            │
```

---

## D. Workflow yang Seharusnya

### Alur Notifikasi yang Benar

```
AdminDesa submit()
  │
  ├─ Batch STATUS_PENDING
  ├─ notificationType='submit'
  ├─ Actor=admin_desa → notifyKecamatanAboutDesaBatch()
  └─ ✅ AdminKecamatan terima "Batch baru dari Admin Desa"
       │
       │ AdminKecamatan review & forward
       ▼
  forwardToKominfo()
  │
  ├─ Batch STATUS_IN_REVIEW
  ├─ notificationType='forward_to_kominfo'
  ├─ Outer if passes ($notificationType !== null)
  └─ ✅ notifyKominfo() → AdminKominfo terima "Batch menunggu verifikasi"
       │
       │ AdminKominfo approve
       ▼
  approve()
  │
  ├─ Batch STATUS_APPROVED
  ├─ notificationType='kecamatan'
  ├─ publish() → audit_trail
  └─ notifyKecamatan() → AdminKecamatan terima "Batch disetujui"
```

### Data Flow yang Benar (Semua Module)

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  DataEntry ──── Direct Write ──── STATUS_DRAFT                  │
│       │                                 │                        │
│       │                          KirimDataKeKominfo              │
│       │                                 │                        │
│       │                    ┌────────────▼────────────┐            │
│       │                    │  SubmissionBatchService│            │
│       │                    │    submit()            │            │
│       │                    │                       │            │
│       │                    │  1. payload_snapshot   │            │
│       │                    │  2. Create items       │            │
│       │                    │  3. STATUS_PENDING     │            │
│       │                    │  4. Notification       │            │
│       │                    └────────────┬────────────┘            │
│       │                                 │                        │
│       │                   ┌─────────────▼─────────────┐          │
│       │                   │    AdminKecamatan Review  │          │
│       │                   │    (for admin_desa data) │          │
│       │                   └─────────────┬─────────────┘          │
│       │                                 │                        │
│       │               ┌───────────────▼───────────────┐        │
│       │               │     AdminKominfo Verify        │        │
│       │               │     approve()                  │        │
│       │               │                               │        │
│       │               │  1. STATUS_APPROVED           │        │
│       │               │  2. publish() → audit_trails   │        │
│       │               │  3. notifyKecamatan()          │        │
│       │               └───────────────┬───────────────┘        │
│       │                               │                        │
│       │                               ▼                        │
│       │                     ┌─────────────────┐               │
│       │                     │  inApprovedBatch│               │
│       │                     │  scope = READY  │               │
│       │                     └─────────────────┘               │
│       │                                                       │
│       ▼                                                       │
│  PUBLIKASI (public portal, API, peta)                         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## E. Gap Analysis

| # | Area | Target | Actual | Severity | Gap |
|---|------|--------|--------|----------|-----|
| 1 | **OPD Workflow** | Semua data melewati batch approval | Kesehatan & Pendidikan bypass workflow | 🔴 CRITICAL | OPD data langsung live tanpa verifikasi. Tidak ada `submission_batch_id` FK. |
| 2 | **Notification** | Kominfo terima notif saat forward | `forward_to_kominfo` dead code | 🟠 HIGH | Nested if `$notificationType === 'submit'` → inner check `$notificationType === 'forward_to_kominfo'` always false |
| 3 | **NIK Uniqueness** | NIK unik per penduduk | Mobile sync tidak validasi | 🔴 CRITICAL | Validasi hanya type-check, tidak ada unique constraint enforcement |
| 4 | **Batch Lock** | lockForUpdate dengan WHERE clause | lockForUpdate dengan `get()` semua record | 🟡 MEDIUM | Performance issue — harus fetch semua row lalu filter di PHP |
| 5 | **Scope Visibility** | Satu pattern scope konsisten | 3 pattern berbeda | 🟡 MEDIUM | `AdminKecamatan` vs `PanelAccess` vs inline closure |
| 6 | **Reject Notification** | AdminDesa tahu saat batch ditolak | Tidak ada notifikasi untuk AdminDesa | 🟢 LOW | `notifyKecamatan()` hanya kirim ke admin_kecamatan |
| 7 | **Role Duplication** | Role check di satu tempat | 3 tempat berbeda | 🟢 LOW | `User.php`, `PanelAccess.php`, `AdminKecamatan.php` |

---

## F. Security Findings

| ID | Severity | Finding | Lokasi | Keterangan | Decision |
|----|----------|---------|--------|------------|----------|
| F1 | 🔴 CRITICAL | Data kesehatan & pendidikan **bypass workflow approval** | Model + Migration | Tidak ada `submission_batch_id` FK. `HasSubmissionBatchApproval` trait tidak efektif karena tidak ada join. Semua data langsung live tanpa verifikasi. | **FIX NOW** |
| F2 | 🔴 CRITICAL | Mobile sync memungkinkan **duplicate NIK** | `MobileSyncController.php:144` | Validasi `nik` hanya `required, string, max:32`. Tidak ada unique check. User bisa submit NIK yang sama berkali-kali. | **FIX NOW** |
| F3 | 🟡 MEDIUM | `submission_number` tidak ada unique rule di model | `SubmissionBatch` | Migration punya unique index tapi model tidak ada rule validasi — risk jika ada programmatic duplicate. | **KEEP AS IS** (DB constraint sudah ada) |
| F4 | 🟢 LOW | Rate limiting untuk mobile sync API tidak ada | `routes/api.php` | Hanya `throttle:api` untuk login. Endpoint push/pull tidak ada throttle terpisah — risk brute force. | **FIX LATER** |
| F5 | 🟢 LOW | Duplicate role definitions di 3 tempat | `AdminKecamatan`, `PanelAccess`, `User.php` | Kode duplikat untuk `isAdminKecamatan()`, `isAdminDesa()`. Maintenance burden. | **REFACTOR LATER** |
| F6 | 🟢 LOW | `operator_puskesmas` & `operator_sekolah` tidak ada di `AVAILABLE_ROLES` | `UserRegistration.php` | Role seeder membuat role ini tapi tidak tersedia untuk registrasi. | **FIX LATER** |

---

## G. Database Findings

| ID | Severity | Finding | Keterangan | Decision |
|----|----------|---------|------------|----------|
| G1 | 🔴 CRITICAL | `data_kesehatan_bulanans` & `data_pendidikan_bulanans` tidak punya `submission_batch_id` FK | OPD data modules completely bypass submission batch approval. Data goes directly from operator to live tables. Trait `HasSubmissionBatchApproval` exists but ineffective without the FK. | **FIX NOW** |
| G2 | 🟡 MEDIUM | Missing unique constraint on `submission_batch_items(batch_id, entity_type, entity_id)` di migration | Migration menunjukkan unique index ada (baris 46: `unique(['entity_type', 'entity_id'])` di `workflow_instances`, bukan di `submission_batch_items`). Pastikan migration `000010` punya unique index yang benar. | **VERIFY** (audit migration) |
| G3 | 🟢 LOW | `scopeInApprovedBatch()` correlated subquery | Subquery kompleks dengan `order by coalesce(...).desc, id desc limit 1` — works correctly but may be slow on large datasets. Perlu index pada `submission_batch_items.submission_batch_id`. | **KEEP AS IS** (works, needs index) |
| G4 | 🟢 LOW | No index on `rumah.publik` + `rumah.latitude` + `rumah.longitude` | Public map query filters on `publik=true` + lat/lng non-zero. Tambahkan composite index. | **FIX LATER** |
| G5 | 🟢 LOW | No database-level enum untuk STATUS fields | Menggunakan application-level constants, database stores plain strings. Acceptable untuk Laravel. | **KEEP AS IS** |

---

## H. Workflow Findings

| ID | Severity | Finding | Lokasi | Keterangan | Decision |
|----|----------|---------|--------|------------|----------|
| H1 | 🟠 HIGH | `forward_to_kominfo` notification **never fires** | `BatchApprovalService.php:107-108` | Nested `if ($notificationType === 'submit')` → inner check `if ($notificationType === 'forward_to_kominfo')` is **always false** karena outer if sudah excluded. Dead code path. | **FIX NOW** |
| H2 | 🟡 MEDIUM | `activeBatchForModule()` performance | `SubmissionBatchService.php:496-510` | `lockForUpdate()->get()` fetches ALL matching records into memory, then filters in PHP. Should use `where()->first()` with lock instead. | **FIX LATER** |
| H3 | 🟢 LOW | `forwardToKominfo()` tidak ubah status | `BatchApprovalService.php:251-259` | `STATUS_IN_REVIEW → STATUS_IN_REVIEW` — semantic confusing, tapi transition sudah diallow dengan special case check. | **KEEP AS IS** |
| H4 | 🟢 LOW | No idempotency for "forward" action | `BatchApprovalService.php` | Jika forward dipanggil 2x, 2 notification tetap dikirim. | **FIX LATER** |

---

## I. Authorization Findings

| ID | Severity | Finding | Keterangan | Decision |
|----|----------|---------|------------|----------|
| I1 | 🟡 MEDIUM | Scope visibility inconsistency | `Penduduk`, `Keluarga`, `Rumah` → `AdminKecamatan` class. `DataKesehatanBulanan`, `DataPendidikanBulanan` → `PanelAccess` class. `SubmissionBatch` → inline closure `scopeVisibleTo()`. 3 pattern berbeda untuk hal yang sama. | **REFACTOR LATER** |
| I2 | 🟡 MEDIUM | Role redundancy | `isAdminKecamatan()`, `isAdminDesa()` didefinisikan di: `User.php`, `PanelAccess.php`, `AdminKecamatan.php`. 3 tempat. | **REFACTOR LATER** |
| I3 | 🟢 LOW | Missing policies | Policies ada untuk `Rumah`, `Keluarga`, `Penduduk`, tapi tidak untuk `DataKesehatanBulanan` & `DataPendidikanBulanan`. | **FIX LATER** |
| I4 | 🟢 LOW | `isAdminKecamatan()` di User model hanya cek role 'admin_kecamatan' | Tidak termasuk 'admin' atau 'super_admin' yang mungkin perlu akses kecamatan. Tapi `canAccessFilament()` di `User.php` sudah include broader roles. | **KEEP AS IS** |

---

## J. Notification Findings

| ID | Severity | Finding | Keterangan | Decision |
|----|----------|---------|------------|----------|
| J1 | 🟠 HIGH | `forward_to_kominfo` notification dead code | `BatchApprovalService.php:107-123`: nested if `if ($notificationType === 'submit')` → inner `if ($notificationType === 'forward_to_kominfo')` can never be true. Kominfo tidak pernah terima notifikasi saat AdminKecamatan meneruskan batch. | **FIX NOW** |
| J2 | 🟢 LOW | No notification for batch rejection sent to AdminDesa | `notifyKecamatan()` hanya kirim ke `admin_kecamatan`. Jika batch dibuat AdminDesa dan ditolak, AdminDesa tidak dapat notifikasi. | **FIX LATER** |
| J3 | 🟢 LOW | `sendNow()` instead of `send()` | Notifications menggunakan `sendNow()` langsung kirim, tidak melalui queue. Jika queue driver diset ke redis, notifications tetap dikirim synchronously. | **KEEP AS IS** (acceptable for current sync driver) |

---

## K. API Findings

| ID | Severity | Finding | Keterangan | Decision |
|----|----------|---------|------------|----------|
| K1 | 🟢 LOW | Mobile sync operates directly on live models, bypassing batch workflow | `MobileSyncController::push()` menulis langsung ke `Rumah`, `Keluarga`, `Penduduk` tanpa melalui `SubmissionBatchService`. Data langsung live tanpa verifikasi. | **KEEP AS IS** (design decision — mobile is for field data entry, batch submission is separate) |
| K2 | 🟢 LOW | No batch-level abstraction for OPD modules in mobile | Mobile bootstrap hanya menyediakan datasets `rumah`, `keluarga`, `penduduk`. Tidak ada data kesehatan/pendidikan. | **KEEP AS IS** |
| K3 | 🟢 LOW | Mobile tidak punya logout invalidation global | `MobileAuthController::logout()` hanya delete current token. Jika device dicuri, token lain tetap valid. | **KEEP AS IS** |

---

## L. Mobile Findings

| ID | Severity | Finding | Lokasi | Keterangan | Decision |
|----|----------|---------|--------|------------|----------|
| L1 | 🔴 CRITICAL | Duplicate NIK bisa disubmit via mobile | `MobileSyncController::storePenduduk():144` | Validasi hanya `'nik' => ['required', 'string', 'max:32']`. Tidak ada unique check. | **FIX NOW** |
| L2 | 🟢 LOW | Mobile data bypasses batch approval | `MobileSyncController::push()` | Data dari mobile langsung masuk ke tabel live. Tidak ada submission batch workflow. Acceptable given design (mobile = field entry, batch = verification). | **KEEP AS IS** |
| L3 | 🟢 LOW | Duplicate key 'role_label' in mobile profile | `MobileAuthController::profile():60` | `role_label` didefinisikan 2x (baris 59 dan 60). JSON akan overwrite. | **FIX NOW** (trivial) |
| L4 | 🟢 LOW | Mobile tidak punya logout invalidation global | `MobileAuthController::logout()` | Hanya delete current token. | **KEEP AS IS** |

---

## M. Testing Findings

| ID | Severity | Finding | Keterangan | Decision |
|----|----------|---------|------------|----------|
| M1 | ✅ GOOD | 7 comprehensive SubmissionBatchTest | Covers: scope limiting, empty submission, snapshot versioning, approval, mixed batch, publish idempotency, rollback, reject, revision | **KEEP** |
| M2 | 🟡 MEDIUM | No test for notification dispatch | Tidak ada test yang verify `BatchNotification` dikirim ke recipient yang tepat. Tidak ada test untuk `forward_to_kominfo`. | **ADD TEST LATER** |
| M3 | 🟡 MEDIUM | No test for NIK uniqueness validation | Tidak ada test yang verify duplicate NIK ditolak. | **ADD TEST LATER** |
| M4 | 🟡 MEDIUM | No test for OPD batch submission | Tidak ada test yang verify kesehatan/pendidikan data ikut batch workflow. | **ADD TEST LATER** |
| M5 | 🟡 MEDIUM | No test for `scopeInApprovedBatch()` correctness | Tidak ada unit test untuk query scope. | **ADD TEST LATER** |
| M6 | 🟢 LOW | `FinalRoleTest` tests seeder output | Test hanya check role names, tidak test actual permission enforcement. | **ADD TEST LATER** |
| M7 | 🟢 LOW | Missing policy tests | Tidak ada test untuk RumahPolicy, KeluargaPolicy, PendudukPolicy. | **ADD TEST LATER** |

---

## N. Architecture Findings

| ID | Severity | Finding | Keterangan | Decision |
|----|----------|---------|------------|----------|
| N1 | ✅ GOOD | SSOT pattern dengan snapshot versioning | Setiap submit membuat JSON snapshot, approved batch dipublish ke `audit_trails`. Immutable audit trail. | **KEEP** |
| N2 | ✅ GOOD | State machine dengan role-based transition | `assertTransitionAllowed()` properly restricts transitions by role and batch status. | **KEEP** |
| N3 | ✅ GOOD | Immutable `audit_trails` table | `timestamps=false`, no UPDATED_AT, only created_at. | **KEEP** |
| N4 | ✅ GOOD | Soft deletes everywhere | SubmissionBatch uses SoftDeletes. Models protected from modification in approved batch. | **KEEP** |
| N5 | ✅ GOOD | Idempotent publish | `SubmissionPublishService::publish()` checks existing publish record before proceeding. | **KEEP** |
| N6 | 🟡 MEDIUM | Inconsistent scope helper classes | 3 class berbeda (`AdminKecamatan`, `PanelAccess`, inline closures) untuk scope visibility yang sama. Maintenance burden. | **REFACTOR LATER** |
| N7 | 🟡 MEDIUM | Role constants duplikasi | AVAILABLE_ROLES di `UserRegistration` vs seeder roles vs `canAccessFilament()`. 3 tempat berbeda. | **REFACTOR LATER** |
| N8 | 🟢 LOW | `scopeInApprovedBatch()` uses raw SQL | Complex correlated subquery with `whereRaw`. Works but fragile. | **KEEP AS IS** (functional) |

---

## O. Technical Debt

| # | Priority | Item | Effort | File |
|---|----------|------|--------|------|
| O1 | 🔴 HIGH | Fix `forward_to_kominfo` notification dead code | < 30 menit | `BatchApprovalService.php` |
| O2 | 🔴 HIGH | Add unique validation on `penduduk.nik` | < 1 jam | `MobileSyncController.php` |
| O3 | 🟡 MEDIUM | Optimize `activeBatchForModule()` — move filter to SQL | < 1 jam | `SubmissionBatchService.php` |
| O4 | 🟡 MEDIUM | Add `submission_batch_id` to OPD tables + integrate workflow | 2-4 jam | Migration + models |
| O5 | 🟡 MEDIUM | Consolidate scope visibility to single pattern | 2-3 jam | Multiple model files |
| O6 | 🟡 MEDIUM | Add missing tests (notifications, forward flow, NIK uniqueness) | 2-3 jam | `tests/Feature/` |
| O7 | 🟢 LOW | Remove duplicate `role_label` key in mobile profile | < 15 menit | `MobileAuthController.php` |
| O8 | 🟢 LOW | Add AdminDesa notification on batch rejection | 1 jam | `BatchApprovalService.php` |
| O9 | 🟢 LOW | Add mobile rate limiting for sync endpoints | 30 menit | `routes/api.php` |
| O10 | 🟢 LOW | Add composite index for public map query | 30 menit | Migration |

---

## P. Prioritas Perbaikan

### 🔴 PHASE 1 — Wajib Segera (Production Data Integrity)

| # | Fix | File | Reason | Status |
|---|-----|------|--------|--------|
| P1.1 | Fix `forward_to_kominfo` notification dead code | `BatchApprovalService.php:107-123` | Kominfo tidak pernah terima notifikasi saat AdminKecamatan meneruskan batch. Workflow broken. | **TODO** |
| P1.2 | Add unique validation on `penduduk.nik` | `MobileSyncController.php:144` | Duplicate NIK breaks data integrity. CRITICAL for NIK-based identification. | **TODO** |
| P1.3 | Remove duplicate `role_label` key | `MobileAuthController.php:60` | Trivial fix. JSON overwrites key. | **TODO** |

### 🟡 PHASE 2 — High Priority (Workflow Integrity)

| # | Fix | File | Reason | Status |
|---|-----|------|--------|--------|
| P2.1 | Add `submission_batch_id` FK to OPD tables | Migration baru | OPD data (kesehatan/pendidikan) bypass seluruh approval workflow. CRITICAL for data governance. | **TODO** |
| P2.2 | Integrate kesehatan/pendidikan into batch submission | `SubmissionBatchService.php` + OPD resources | Ensure OPD data follows same approval flow as rumah/keluarga/penduduk. | **TODO** |
| P2.3 | Add test for `forward_to_kinfo` notification | `SubmissionBatchTest.php` | Regression prevention. | **TODO** |
| P2.4 | Add NIK uniqueness test | New test | Regression prevention. | **TODO** |
| P2.5 | Optimize `activeBatchForModule()` | `SubmissionBatchService.php:496` | lockForUpdate + get() all → WHERE clause in SQL. | **TODO** |

### 🟢 PHASE 3 — Medium Priority (Consistency & Maintainability)

| # | Fix | Target | Reason | Status |
|---|-----|--------|--------|--------|
| P3.1 | Consolidate scope visibility to single pattern | Multiple model files | 3 pattern berbeda → 1 pattern. Reduce maintenance burden. | **TODO** |
| P3.2 | Consolidate role constants | 3 file berbeda | AVAILABLE_ROLES di 3 tempat → 1 constant class/enum. | **TODO** |
| P3.3 | Add AdminDesa notification on rejection | `BatchApprovalService.php` | AdminDesa needs to know when their submission is rejected. | **TODO** |
| P3.4 | Add mobile sync rate limiting | `routes/api.php` | Security hardening. | **TODO** |
| P3.5 | Add composite index for public map | Migration baru | Performance optimization. | **TODO** |
| P3.6 | Add OPD policies | `app/Policies/` | Missing authorization for kesehatan/pendidikan models. | **TODO** |

---

## Q. KEPUTUSAN TEKNIS AKHIR

### ✅ TETAP (KEEP AS IS)

| # | Item | Alasan |
|---|------|--------|
| Q1 | Laravel + Filament stack | Stack sudah proven untuk use case ini. Tidak ada alasan ganti. |
| Q2 | MySQL over PostgreSQL | MySQL sudah berjalan, tidak ada kebutuhan PostgreSQL-specific features (JSONB, full-text search tidak critical untuk saat ini). |
| Q3 | Spatie Permission untuk RBAC | Pattern sudah correct. Duplikasi dengan `AdminKecamatan`/`PanelAccess` adalah technical debt minor, bukan architectural flaw. |
| Q4 | JSON snapshot storage (bukan dedicated snapshot tables) | Snapshot versioning sudah bekerja dengan baik. Immutable `audit_trails` menangkap perubahan saat publish. |
| Q5 | Database notifications only (bukan email/SMS) | BatchNotification hanya database — sesuai dengan use case. Admin panel notification sudah cukup. |
| Q6 | sync queue driver (untuk saat ini) | Production bisa upgrade ke Redis queue, tapi current setup acceptable untuk small-medium deployment. Priority upgrade path ada di `docs/PRODUCTION_ARCHITECTURE.md`. |
| Q7 | Single monolith application | Tidak ada kebutuhan microservices. OPD integration cukup dengan module-based separation dalam monolith. |
| Q8 | Mobile direct write (bypass batch) | Design decision yang reasonable — mobile untuk field data entry, batch submission adalah langkah verifikasi terpisah. Tidak perlu diubah. |

### ⚠️ PERBAIKI SEKARANG (FIX NOW)

| # | Item | File | Reason |
|---|------|------|--------|
| Q9 | **Fix notification dead code** (`forward_to_kominfo`) | `app/Services/BatchApprovalService.php` | `forward_to_kominfo` tidak pernah diproses — nested if `if ($notificationType === 'submit')` → inner check `$notificationType === 'forward_to_kominfo'` always false. Kominfo tidak pernah dikabari saat batch diteruskan. |
| Q10 | **Fix NIK uniqueness validation** | `app/Http/Controllers/Api/MobileSyncController.php` | CRITICAL data integrity issue. Mobile sync memungkinkan duplicate NIK tanpa ada validasi. |

### 🔧 PERBAIKI NANTI (FIX LATER)

| # | Item | Target Sprint | Reason |
|---|------|---------------|--------|
| Q11 | OPD batch integration | vNext sprint | OPD data tidak melewati approval workflow — perlu dirancang dan diimplementasi. Priority HIGH tapi perlu planning. |
| Q12 | Scope visibility consolidation | vNext sprint | 3 pattern berbeda untuk scope visibility yang sama — technical debt yang perlu di-solve. |
| Q13 | Performance: `activeBatchForModule()` | vNext sprint | lockForUpdate + get() semua row — perlu WHERE clause di SQL. Impact saat batch count meningkat. |
| Q14 | Missing tests | vNext sprint | Notification dispatch, forward flow, NIK uniqueness, OPD batch — perlu coverage. |

### 🔄 REFACTOR

| # | Item | Target Sprint | Reason |
|---|------|---------------|--------|
| Q15 | Role constants consolidation | vNext sprint | AVAILABLE_ROLES di 3 tempat berbeda (`UserRegistration`, seeder, `canAccessFilament()`) → 1 centralized location. |
| Q16 | Scope helper class consolidation | vNext sprint | `AdminKecamatan` + `PanelAccess` + inline closures → single helper class dengan interface yang konsisten. |

---

## Ringkasan Eksekutif

**3 Fix kritis** yang perlu dilakukan segera (total effort < 2 jam):

```
┌─────────────────────────────────────────────────────────────┐
│ Q9  Fix notification dead code                             │
│     File: app/Services/BatchApprovalService.php:107-123     │
│     Effort: < 30 menit                                      │
│     Impact: Kominfo terima notifikasi saat batch diteruskan  │
├─────────────────────────────────────────────────────────────┤
│ Q10 Fix NIK uniqueness validation                           │
│     File: app/Http/Controllers/Api/MobileSyncController.php │
│     Effort: < 1 jam                                         │
│     Impact: Data penduduk NIK menjadi unik & valid         │
├─────────────────────────────────────────────────────────────┤
│ L3  Remove duplicate role_label key                        │
│     File: app/Http/Controllers/Api/MobileAuthController.php │
│     Effort: < 15 menit                                     │
│     Impact: API response clean tanpa duplicate keys         │
└─────────────────────────────────────────────────────────────┘
```

**3 Refactor yang sebaiknya dilakukan** (total effort ~6-8 jam):

```
┌─────────────────────────────────────────────────────────────┐
│ Q11 OPD batch integration                                  │
│     Files: Migration baru + OPD models + service           │
│     Effort: 2-4 jam                                         │
│     Impact: Kesehatan & pendidikan masuk workflow approval   │
├─────────────────────────────────────────────────────────────┤
│ Q12 Scope visibility consolidation                         │
│     Files: Multiple model files                             │
│     Effort: 2-3 jam                                         │
│     Impact: 1 pattern scope yang konsisten                 │
├─────────────────────────────────────────────────────────────┤
│ Q15 Role constants consolidation                            │
│     Files: UserRegistration + canAccessFilament + seeder   │
│     Effort: 1-2 jam                                         │
│     Impact: Role definitions di satu tempat                │
└─────────────────────────────────────────────────────────────┘
```

**Prinsip yang TETAP DILESTARIKAN:**
- SSOT pattern dengan snapshot versioning ✅
- Immutable audit trail ✅
- State machine batch approval ✅
- RBAC dengan Spatie Permission ✅
- Soft deletes everywhere ✅
- Idempotent publish ✅

**Prinsip yang TIDAK PERLU DIGANTI:**
- microservices ❌ (tidak ada kebutuhan)
- PostgreSQL ❌ (MySQL sudah cukup)
- Dedicated snapshot tables ❌ (JSON sudah bekerja)
- Email/SMS notifications ❌ (database notification sudah cukup)
- Queue driver upgrade ❌ (sync acceptable untuk saat ini)

---

*Document ini adalah hasil audit teknis mendalam terhadap codebase SIPANDU. Rekomendasi didasarkan pada analisis actual source code, bukan asumsi. Semua decision mengikuti prinsip user: "Jangan mengubah architecture hanya karena terlihat lebih modern, membuat microservices tanpa kebutuhan, membuat abstraction berlebihan."*