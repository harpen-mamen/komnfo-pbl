# SIPANDU IMPLEMENTATION BLUEPRINT
## Arsitektur Final & Spec Perbaikan — Agustus 2026

> **Dokumen ini adalah hasil akhir audit teknis + rancangan solusi SIPANDU**
> 
> **Fungsi**: Blueprint implementasi untuk semua perbaikan prioritas tinggi
> **Status**: Draft — menunggu persetujuan sebelum implementasi
> **Auditor**: Claude Code (claude-opus-4-6)
> **Tanggal**: 8 Agustus 2026
> **Version**: 1.0

---

## DAFTAR ISI

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Arsitektur Final Target](#2-arsitektur-final-target)
3. [SPEC-001: Fix Notification Dead Code](#spec-001-fix-forward_to_kominfo-notification)
4. [SPEC-002: Fix NIK Uniqueness](#spec-002-nik-uniqueness-validation)
5. [SPEC-003: OPD Batch Workflow Integration](#spec-003-opd-batch-workflow-integration)
6. [SPEC-004: Scope Visibility Consolidation](#spec-004-scope-visibility-consolidation)
7. [SPEC-005: Role Constants Refactor](#spec-005-role-constants-refactor)
8. [SPEC-006: Optimize activeBatchForModule](#spec-006-optimize-activebatchformodule)
9. [SPEC-007: Fix Mobile Profile & Rate Limiting](#spec-007-mobile-improvements)
10. [SPEC-008: AdminDesa Rejection Notification](#spec-008-admindesa-rejection-notification)
11. [Migrations & Database Changes](#migrations--database-changes)
12. [Testing Strategy](#testing-strategy)
13. [Rollback Plan](#rollback-plan)
14. [Implementation Roadmap](#implementation-roadmap)

---

## 1. Ringkasan Eksekutif

### Kondisi Saat Ini

```
Arsitektur SIPANDU memiliki fondasi yang SOLID:
├── ✅ SSOT pattern dengan snapshot versioning
├── ✅ Immutable audit trail
├── ✅ State machine batch approval
├── ✅ RBAC dengan Spatie (38 roles)
├── ✅ Soft deletes everywhere
├── ✅ Payload snapshot versioning
├── ✅ Idempotent publish
├── ✅ 7 comprehensive tests
└── ✅ Laravel Sanctum mobile auth

TAPI memiliki 5 masalah teknis yang perlu perbaikan:
├── 🔴 A1: Notification dead code (Kominfo tidak pernah dikabari)
├── 🔴 A2: NIK uniqueness tidak ada (mobile sync)
├── 🟠 A3: OPD data bypass workflow approval (kesehatan/pendidikan)
├── 🟡 A7: lockForUpdate + get() semua row (performance)
└── 🟡 A8: Scope visibility 3 pattern berbeda (inconsistency)
```

### Dampak Bisnis

| Issue | Dampak |
|-------|--------|
| A1: Kominfo tidak dikabari | Batch dari kecamatan menumpuk tanpa verifikasi. Workflow approval tidak efektif. |
| A2: Duplicate NIK | Data kependudukan rusak. Tidak bisa melakukan analisis demografi yang akurat. |
| A3: OPD bypass workflow | Data kesehatan/pendidikan langsung live tanpa verifikasi. Tidak ada accountability. |

### Solution Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    BEFORE → AFTER                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  A1: Notification Dead Code                                     │
│  ├── BEFORE: nested if → forward_to_kominfo never executes      │
│  └── AFTER:  separate outer if → correct dispatch path          │
│                                                                 │
│  A2: NIK Uniqueness                                             │
│  ├── BEFORE: nik validation = ['required','string','max:32']    │
│  └── AFTER:  nik validation + unique check per desa             │
│                                                                 │
│  A3: OPD Workflow Integration                                   │
│  ├── BEFORE: no FK, no batch items, trait ineffective           │
│  └── AFTER:  submission_batch_id FK + batch item creation        │
│                                                                 │
│  A7: Batch Lock Performance                                      │
│  ├── BEFORE: lockForUpdate()->get()->first()                    │
│  └── AFTER:  lockForUpdate()->where()->first()                  │
│                                                                 │
│  A8: Scope Visibility                                            │
│  ├── BEFORE: AdminKecamatan + PanelAccess + closure (3 patterns) │
│  └── AFTER:  Single Scoper class (1 consistent pattern)          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Arsitektur Final Target

### 2.1 Arsitektur Sistem Keseluruhan (Sesudah Perbaikan)

```
┌──────────────────────────────────────────────────────────────────────┐
│                         FILAMENT ADMIN PANELS                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐               │
│  │   KOMINFO   │  │  KECAMATAN   │  │    DESA      │               │
│  │   Panel     │  │   Panel      │  │   Panel      │               │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘               │
│         │                  │                  │                        │
│         └──────────────────┴──────────────────┘                        │
│                            │                                          │
│                 ┌──────────▼──────────┐                               │
│                 │ KirimDataKeKominfo │                               │
│                 │  (Batch Submit)    │                               │
│                 └──────────┬──────────┘                               │
└────────────────────────────┼──────────────────────────────────────────┘
                             │
┌────────────────────────────▼──────────────────────────────────────────┐
│                      SUBMISSION BATCH SERVICE                           │
│                                                                       │
│  Workflow: DRAFT → PENDING → IN_REVIEW → APPROVED / REJECTED / ARCH   │
│                                                                       │
│  Notification Dispatch:                                                 │
│  ├── AdminDesa submit  → notifyKecamatanAboutDesaBatch() ✅           │
│  ├── AdminKec forward  → notifyKominfo() ✅  ← FIXED (A1)             │
│  ├── Kominfo approves  → notifyKecamatan(approved) ✅                  │
│  └── Kominfo rejects   → notifyKecamatan(rejected) ✅                 │
│                          + notifyAdminDesa(rejected) ✅  ← NEW (A10)  │
└──────┬───────────┬───────────┬───────────┬────────────────────────────┘
       │           │           │           │
       ▼           ▼           ▼           ▼
   ┌────────┐ ┌────────┐ ┌────────────┐ ┌─────────────┐
   │ Rumah  │ │Keluarga│ │ Penduduk  │ │ KESEHATAN  │
   │        │ │        │ │           │ │            │
   │  ✅    │ │   ✅   │ │    ✅     │ │    ✅      │
   │ WS+NIK │ │  WS    │ │ WS+Unique │ │ WS (A3 FIX)
   └────────┘ └────────┘ └───────────┘ └─────────────┘
                                           │
                                           ▼
                                    ┌─────────────┐
                                    │PENDIDIKAN   │
                                    │             │
                                    │    ✅       │
                                    │WS (A3 FIX)  │
                                    └─────────────┘
                                 WS = Workflow Submission
```

### 2.2 Arsitektur Data Layer (Sesudah Perbaikan)

```
┌─────────────────────────────────────────────────────────────────────┐
│                    SUBMISSION BATCH (Source of Truth)                │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  submission_batches                                           │  │
│  │  ├── id (PK)                                                 │  │
│  │  ├── module (rumah/keluarga/penduduk/kesehatan/pendidikan)   │  │
│  │  ├── status (draft/pending/in_review/approved/rejected)     │  │
│  │  ├── payload_snapshot (JSON)                                 │  │
│  │  ├── version (integer)                                       │  │
│  │  ├── kecamatan_id (FK)                                       │  │
│  │  └── ...                                                     │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                              │                                         │
│                              │ 1:N                                       │
│                              ▼                                         │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  submission_batch_items (Polymorphic Join Table)             │  │
│  │  ├── id (PK)                                                │  │
│  │  ├── submission_batch_id (FK → submission_batches)         │  │
│  │  ├── package_key (module name)                              │  │
│  │  ├── entity_type (Rumah/Keluarga/Penduduk/                  │  │
│  │  │             DataKesehatanBulanan/DataPendidikanBulanan)   │  │
│  │  ├── entity_id (PK of target entity)                        │  │
│  │  └── unique(batch_id, entity_type, entity_id)               │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                              │                                         │
│          ┌───────────────────┼───────────────────┐                      │
│          │                   │                   │                      │
│          ▼                   ▼                   ▼                      │
│    ┌──────────┐       ┌──────────────┐    ┌─────────────────┐         │
│    │  Rumah   │       │   Penduduk   │    │DataKesehatanBul │         │
│    │          │       │              │    │                 │         │
│    │ +NIK     │       │ +NIK Unique  │    │ submission_     │← NEW    │
│    │ Unique   │       │ per desa     │    │ batch_id (FK)  │   (A3)  │
│    └──────────┘       └──────────────┘    └─────────────────┘         │
│                                                      │                  │
│                                              ┌──────────────┐           │
│                                              │DataPendidBul │           │
│                                              │              │           │
│                                              │ submission_   │← NEW      │
│                                              │ batch_id (FK)│   (A3)   │
│                                              └──────────────┘           │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.3 Arsitektur Notification Flow (Sesudah Perbaikan)

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    NOTIFICATION FLOW — FINAL                                │
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ Actor: AdminDesa                                                 │    │
│  │ Action: submit() → STATUS_PENDING                               │    │
│  │ NotificationType: 'submit'                                      │    │
│  │                                                                   │    │
│  │ if ($notificationType === 'submit') {  ✅                        │    │
│  │     if ($actor->hasRole('admin_desa')) {                        │    │
│  │         notifyKecamatanAboutDesaBatch()  → AdminKecamatan       │    │
│  │     } else {                                                    │    │
│  │         notifyKominfo()  → AdminKominfo                          │    │
│  │     }                                                            │    │
│  │ }                                                                │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                                  │                                        │
│                                  ▼                                        │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ Actor: AdminKecamatan                                            │    │
│  │ Action: forwardToKominfo() → STATUS_IN_REVIEW                   │    │
│  │ NotificationType: 'forward_to_kominfo'                           │    │
│  │                                                                   │    │
│  │ if ($notify && $notificationType !== null) {  ✅                 │    │
│  │     if ($notificationType === 'submit') {                        │    │
│  │         // handled above                                         │    │
│  │     } elseif ($notificationType === 'forward_to_kominfo') {      │    │
│  │         notifyKominfo()  → AdminKominfo  ✅  ← FIXED (A1)        │    │
│  │     } else {                                                    │    │
│  │         notifyKecamatan()                                         │    │
│  │     }                                                            │    │
│  │ }                                                                │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                                  │                                        │
│                                  ▼                                        │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ Actor: AdminKominfo                                              │    │
│  │ Action: approve() → STATUS_APPROVED                              │    │
│  │ NotificationType: 'kecamatan'                                   │    │
│  │                                                                   │    │
│  │ if ($notify && $notificationType !== null) {  ✅                 │    │
│  │     if ($notificationType === 'submit') { ... }                  │    │
│  │     elseif ($notificationType === 'forward_to_kominfo') { ... }   │    │
│  │     else {                                                      │    │
│  │         notifyKecamatan(approved)  → AdminKecamatan               │    │
│  │     }                                                            │    │
│  │ }                                                                │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                                  │                                        │
│                                  ▼                                        │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ Actor: AdminKominfo                                              │    │
│  │ Action: reject() → STATUS_REJECTED                               │    │
│  │ NotificationType: 'kecamatan'                                   │    │
│  │                                                                   │    │
│  │ if ($notify && $notificationType !== null) {  ✅                 │    │
│  │     if ($notificationType === 'submit') { ... }                  │    │
│  │     elseif ($notificationType === 'forward_to_kominfo') { ... }   │    │
│  │     else {                                                      │    │
│  │         notifyKecamatan(rejected)                                │    │
│  │         + notifyAdminDesaIfRelevant(batch)  ✅  ← NEW (A10)     │    │
│  │     }                                                            │    │
│  │ }                                                                │    │
│  └─────────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────────┘
```

### 2.4 Arsitektur Scope Visibility (Sesudah Perbaikan)

```
┌────────────────────────────────────────────────────────────────────────┐
│                SCOPE VISIBILITY — FINAL PATTERN                        │
│                                                                         │
│  BEFORE (3 patterns):                                                   │
│  ├── Rumah/Keluarga/Penduduk → AdminKecamatan class                   │
│  ├── DataKesehatanBulanan    → PanelAccess class                       │
│  └── SubmissionBatch         → Inline closure (scopeVisibleTo)          │
│                                                                         │
│  AFTER (1 pattern):                                                     │
│  └── ALL models → UserScope class (single source of truth)            │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │  app/Support/UserScope.php                                       │   │
│  │                                                                   │   │
│  │  class UserScope                                                 │   │
│  │  {                                                                │   │
│  │      public static function visibleTo(Builder $query): Builder    │   │
│  │      public static function getKecamatanId(): ?int              │   │
│  │      public static function getDesaId(): ?int                    │   │
│  │      public static function getUser(): ?User                     │   │
│  │      public static function isKabupatenLevel(): bool            │   │
│  │      public static function isKecamatanLevel(): bool            │   │
│  │      public static function isDesaLevel(): bool                  │   │
│  │      public static function isOpdLevel(): bool                   │   │
│  │  }                                                                │   │
│  │                                                                   │   │
│  │  // Usage in models:                                              │   │
│  │  public function scopeVisibleToCurrentUser(Builder $query)      │   │
│  │  {                                                                │   │
│  │      return UserScope::visibleTo($query);                        │   │
│  │  }                                                                │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  Role check helpers tetap di PanelAccess.php (authorized actions only)  │
│  Scope helpers dipindahkan ke UserScope.php (data visibility only)     │
└────────────────────────────────────────────────────────────────────────┘
```

---

## SPEC-001: Fix forward_to_kominfo Notification

### A. Problem Statement

```php
// BatchApprovalService.php lines 105-125 — BEFORE

if ($notify && $notificationType !== null) {
    DB::afterCommit(function () use ($updatedBatch, $actor, $notificationType): void {
        if ($notificationType === 'submit') {           // ← OUTER if
            if ($notificationType === 'forward_to_kominfo') {  // ← NEVER TRUE
                $this->notifyKominfo($updatedBatch, $actor);      // ← DEAD CODE
                return;
            }

            if ($actor->hasRole('admin_desa')) {
                $this->notifyKecamatanAboutDesaBatch($updatedBatch, $actor);
            } else {
                $this->notifyKominfo($updatedBatch, $actor);
            }

            return;
        }

        $this->notifyKecamatan($updatedBatch, $updatedBatch->status, $actor);
    });
}
```

**Problem**: `forwardToKominfo()` memanggil `transition(..., 'forward_to_kominfo')`. Tapi di dalam blok `if ($notificationType === 'submit')`, tidak mungkin `$notificationType === 'forward_to_kominfo'`. Result: Kominfo tidak pernah dikabari saat batch diteruskan.

### B. Solution Design

```php
// BatchApprovalService.php — AFTER

if ($notify && $notificationType !== null) {
    DB::afterCommit(function () use ($updatedBatch, $actor, $notificationType): void {
        if ($notificationType === 'submit') {
            if ($actor->hasRole('admin_desa')) {
                // AdminDesa submit → Kabari AdminKecamatan
                $this->notifyKecamatanAboutDesaBatch($updatedBatch, $actor);
            } else {
                // AdminKecamatan/Admin submit langsung → Kabari Kominfo
                $this->notifyKominfo($updatedBatch, $actor);
            }

            return;
        }

        if ($notificationType === 'forward_to_kominfo') {
            // AdminKecamatan forward ke Kominfo → Kabari Kominfo  ← FIXED
            $this->notifyKominfo($updatedBatch, $actor);

            return;
        }

        // approve/reject/revise/archive → Kabari AdminKecamatan
        $this->notifyKecamatan($updatedBatch, $updatedBatch->status, $actor);
    });
}
```

### C. Change Location

| File | Line | Change |
|------|------|--------|
| `app/Services/BatchApprovalService.php` | 105-125 | Refactor notification dispatch block |

### D. Implementation Steps

1. Hapus blok nested `if ($notificationType === 'submit')` yang dalam
2. Pisahkan 3 case secara flat: `submit`, `forward_to_kominfo`, `else` (approve/reject/...)
3. Test: verify Kominfo terima notifikasi saat AdminKecamatan forward batch

### E. Testing

```php
public function test_kominfo_receives_notification_when_kecamatan_forwards_batch(): void
{
    Notification::fake();

    $sender = User::factory()->create();
    $sender->assignRole('admin_kecamatan');
    $sender->kecamatan_id = $kecamatan->id;
    $sender->save();

    $kominfoUser = User::factory()->create();
    $kominfoUser->assignRole('admin_kominfo');

    // ... create batch ...

    app(BatchApprovalService::class)->forwardToKominfo($batch, $sender);

    Notification::assertSentTo(
        $kominfoUser,
        BatchNotification::class,
        fn (BatchNotification $n) => str_contains($n->title, 'menunggu verifikasi')
    );
}
```

### F. Rollback

Comment out perubahan, revert ke nested if. Notification tetap jalan tapi forward tidak.

---

## SPEC-002: NIK Uniqueness Validation

### A. Problem Statement

```php
// MobileSyncController.php line 141-157 — BEFORE

$data = Validator::make($payload, [
    'nik' => ['required', 'string', 'max:32'],  // ← NO UNIQUE CHECK
    // ...
])->validate();
```

**Problem**: Mobile sync memungkinkan NIK duplikat. Setiap user bisa submit NIK yang sama berkali-kali. Ini merusak data integrity untuk NIK-based identification.

### B. Solution Design

Terdapat 2 opsi. Pilih **Opsi A** (paling sederhana & efektif):

#### Opsi A: Validate Against Existing Penduduk (REKOMENDASI)

```php
// MobileSyncController.php — AFTER

$data = Validator::make($payload, [
    'nik' => [
        'required',
        'string',
        'max:32',
        // Cek NIK tidak terdaftar di wilayah lain dalam kecamatan yang sama
        function ($attribute, $value, $fail) use ($request) {
            $existing = Penduduk::query()
                ->where('nik', $value)
                ->whereHas('keluarga.rumah.desa', function ($q) use ($request) {
                    $q->where('kecamatan_id', $request->user()->kecamatan_id);
                })
                ->first();

            if ($existing) {
                // Jika milik user yang sama tapi entity berbeda (update scenario)
                // Biarkan lewat — ini adalah kasus update
                $payloadNik = $request->input('operations.*.payload.nik');
                // Untuk create baru, NIK tidak boleh sudah ada
                // Check dilakukan di storePenduduk berdasarkan local_id
            }
        },
    ],
    // ...
])->validate();

// Di dalam storePenduduk(), cek NIK tidak ada di database
private function storePenduduk(Request $request, array $payload): int
{
    // Duplicate check: NIK tidak boleh sudah ada di kecamatan yang sama
    $existingNik = Penduduk::query()
        ->where('nik', $payload['nik'])
        ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $request->user()->kecamatan_id))
        ->exists();

    if ($existingNik) {
        throw ValidationException::withMessages([
            'nik' => 'NIK sudah terdaftar di wilayah ini.',
        ]);
    }

    // ... rest of method ...
}
```

#### Opsi B: Add Database Unique Constraint

Tambahkan migration untuk add unique index:

```php
// Migration — opsional, untuk enforcement di level database
Schema::table('penduduk', function (Blueprint $table) {
    $table->unique(['nik', 'kecamatan_id'], 'penduduk_nik_kecamatan_unique');
});
```

**Catatan**: Opsi B perlu hati-hati karena satu NIK bisa sama di kecamatan berbeda (skenario: warga pindah). Rekomendasi: Opsi A dengan validation di aplikasi sudah cukup.

### C. Change Location

| File | Line | Change |
|------|------|--------|
| `app/Http/Controllers/Api/MobileSyncController.php` | 141-157 | Add NIK uniqueness validation |
| `app/Http/Controllers/Api/MobileSyncController.php` | 139-176 | Add duplicate check di storePenduduk() |

### D. Implementation Steps

1. Tambahkan unique check di `storePenduduk()` sebelum create
2. Handle update scenario: jika penduduk sudah ada dengan `local_id` yang sama, skip check
3. Test: verify duplicate NIK ditolak dengan proper error message

### E. Testing

```php
public function test_mobile_sync_rejects_duplicate_nik(): void
{
    $user = User::factory()->create(['role' => 'admin_desa', 'desa_id' => $desa->id]);
    
    // User 1: Submit NIK 1234567890123456
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/mobile/sync/push', [
            'device_id' => 'device-1',
            'operations' => [[
                'operation_id' => Uuid::uuid4()->toString(),
                'dataset' => 'penduduk',
                'local_id' => 'local-1',
                'payload' => ['nik' => '1234567890123456', /* ... */],
            ]],
        ])
        ->assertJsonPath('results.0.status', 'accepted');

    // User 2 (different desa, same kecamatan): Submit SAME NIK → should FAIL
    $user2 = User::factory()->create(['role' => 'admin_desa', 'desa_id' => $desa2->id]);
    // Note: ini scenario yang complex — NIK validasi per kecamatan
    // perlu discussion dengan stakeholder
}
```

### F. Edge Cases

| Scenario | Behavior |
|----------|----------|
| Same NIK, same kecamatan, different user | ❌ Reject — NIK sudah ada |
| Same NIK, different kecamatan | ✅ Allow — warga bisa punya NIK yang sama di kecamatan berbeda |
| Same NIK, same user, operation_id baru (re-submit) | ❌ Reject — duplicate dalam kecamatan yang sama |
| Same NIK, same user, same local_id (retry) | ✅ Allow — idempotent berdasarkan operation_id |

### G. Rollback

Hapus unique check di `storePenduduk()`. Revert ke validation rule saja.

---

## SPEC-003: OPD Batch Workflow Integration

### A. Problem Statement

```
┌─────────────────────────────────────────────────────────────┐
│  BEFORE: Kesehatan & Pendidikan Bypass Workflow               │
│                                                              │
│  DataKesehatanBulanan                                        │
│  ├── HasSubmissionBatchApproval trait ✅                    │
│  ├── Has submissionBatchItems() relation ✅                  │
│  ├── BUT: no submission_batch_id column ❌                  │
│  └── BUT: no items created on submit ❌                     │
│                                                              │
│  DataPendidikanBulanan                                       │
│  ├── HasSubmissionBatchApproval trait ✅                    │
│  ├── Has submissionBatchItems() relation ✅                  │
│  ├── BUT: no submission_batch_id column ❌                  │
│  └── BUT: no items created on submit ❌                     │
│                                                              │
│  Impact: Data langsung live tanpa verifikasi Kominfo          │
└─────────────────────────────────────────────────────────────┘
```

### B. Solution Design

Tambahkan `submission_batch_id` FK ke tabel OPD dan modifikasi `SubmissionBatchService` untuk membuat batch items untuk OPD data.

#### Step 1: Database Migration

```php
// database/migrations/2026_08_08_000001_add_submission_batch_fk_to_opd_tables.php

public function up(): void
{
    // Add submission_batch_id to data_kesehatan_bulanans
    Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
        $table->foreignId('submission_batch_id')
            ->nullable()
            ->after('kecamatan_id')
            ->constrained('submission_batches')
            ->nullOnDelete();
        
        $table->index(['submission_batch_id', 'kecamatan_id', 'periode_bulan', 'periode_tahun']);
    });

    // Add submission_batch_id to data_pendidikan_bulanans
    Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
        $table->foreignId('submission_batch_id')
            ->nullable()
            ->after('kecamatan_id')
            ->constrained('submission_batches')
            ->nullOnDelete();
        
        $table->index(['submission_batch_id', 'kecamatan_id', 'semester', 'periode_tahun']);
    });
}

public function down(): void
{
    Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
        $table->dropForeign(['submission_batch_id']);
        $table->dropColumn('submission_batch_id');
    });

    Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
        $table->dropForeign(['submission_batch_id']);
        $table->dropColumn('submission_batch_id');
    });
}
```

#### Step 2: Model Update

```php
// app/Models/DataKesehatanBulanan.php — ADD

class DataKesehatanBulanan extends Model
{
    // ... existing code ...

    // ADD: submission_batch FK
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }
}

// app/Models/DataPendidikanBulanan.php — ADD

class DataPendidikanBulanan extends Model
{
    // ... existing code ...

    // ADD: submission_batch FK
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }
}
```

#### Step 3: SubmissionBatchService Update

```php
// app/Services/SubmissionBatchService.php

// Di method submit(), saat membuat batch items untuk MODULE_KESEHATAN
// atau MODULE_PENDIDIKAN, set submission_batch_id pada setiap record

private function createMixedItems(SubmissionBatch $batch, Collection $modulePayloads): void
{
    $rows = [];
    $opdBatchAssignments = [];  // NEW: Track OPD record assignments

    foreach ($modulePayloads as $payload) {
        $entityType = $this->entityTypeForModule($payload['module']);

        if (! $entityType) {
            continue;
        }

        // Build batch item rows
        $this->appendEntityRows($rows, $batch->id, $payload['ids'], $entityType, $payload['module']);

        // NEW: Assign submission_batch_id to OPD records
        if (in_array($payload['module'], [self::MODULE_KESEHATAN, self::MODULE_PENDIDIKAN])) {
            $opdBatchAssignments[] = [
                'model' => $entityType,
                'ids' => $payload['ids'],
                'batch_id' => $batch->id,
            ];
        }
    }

    if ($rows !== []) {
        SubmissionBatchItem::query()->insert($rows);
    }

    // NEW: Update OPD records with submission_batch_id
    foreach ($opdBatchAssignments as $assignment) {
        $assignment['model']::query()
            ->whereKey($assignment['ids'])
            ->update(['submission_batch_id' => $assignment['batch_id']]);
    }
}
```

#### Step 4: Submit to Kominfo Flow

Untuk OPD data, flow menjadi:

```
operator_puskes → KirimDataKeKominfo → STATUS_PENDING → STATUS_APPROVED → publish
                                                         ↓
                                                    AdminKominfo approves
                                                    (kirim notifikasi ke OPD)
```

Batasan: OPD data (kesehatan/pendidikan) dikirim langsung oleh `operator_puskes` atau `operator_sekolah` ke Kominfo tanpa melewati AdminKecamatan. Ini konsisten dengan desain OPD (DINKES/DISDIK adalah unit independent).

### C. Change Location

| File | Change |
|------|--------|
| `database/migrations/2026_08_08_000001_add_submission_batch_fk_to_opd_tables.php` | New migration |
| `app/Models/DataKesehatanBulanan.php` | Add submissionBatch() relation |
| `app/Models/DataPendidikanBulanan.php` | Add submissionBatch() relation |
| `app/Services/SubmissionBatchService.php` | Update createMixedItems() untuk assign batch_id |
| `app/Filament/Resources/DataKesehatanBulananResource.php` | Tampilkan batch status badge |
| `app/Filament/Resources/DataPendidikanBulananResource.php` | Tampilkan batch status badge |

### D. Implementation Steps

1. **Phase 1**: Buat migration, add FK + index
2. **Phase 2**: Update model dengan relation
3. **Phase 3**: Update `createMixedItems()` di `SubmissionBatchService`
4. **Phase 4**: Update Filament resources untuk tampilkan batch status
5. **Phase 5**: Test end-to-end workflow
6. **Phase 6**: Existing data — buat artisan command untuk assign existing records ke batch baru jika perlu

### E. Existing Data Handling

```bash
# Artisan command untuk assign existing OPD data ke batch
php artisan opd:backfill-batch-id \
    --module=kesehatan \
    --from-date=2026-01-01 \
    --dry-run
```

### F. Testing

```php
public function test_kesehatan_data_is_linked_to_submission_batch(): void
{
    // Admin puskes submit batch
    $batches = app(SubmissionBatchService::class)
        ->submit($kecamatan->id, [SubmissionBatch::MODULE_KESEHATAN], 5, null, 2026, 'test', $user);

    $batch = $batches->first();

    // Verify: setiap DataKesehatanBulanan punya submission_batch_id
    $kesehatan = DataKesehatanBulanan::query()
        ->where('kecamatan_id', $kecamatan->id)
        ->where('periode_bulan', 5)
        ->where('periode_tahun', 2026)
        ->get();

    $this->assertTrue($kesehatan->every(fn ($k) => $k->submission_batch_id === $batch->id));
}

public function test_kesehatan_data_cannot_be_modified_after_approval(): void
{
    // Submit, approve batch
    $batch = /* ... */;
    app(BatchApprovalService::class)->approve($batch, $approver);

    // Try to modify kesehatan data → should fail
    $kesehatan = DataKesehatanBulanan::first();
    $kesehatan->stunting = 999;
    
    $this->expectException(AuthorizationException::class);
    $kesehatan->save();  // Should throw
}
```

### G. Rollback

1. Rollback migration (hapus FK column)
2. Hapus `submissionBatch()` relation dari model
3. Hapus batch assignment logic dari `SubmissionBatchService`

---

## SPEC-004: Scope Visibility Consolidation

### A. Problem Statement

```
┌──────────────────────────────────────────────────────────────┐
│  BEFORE: 3 different scope patterns                          │
│                                                               │
│  Pattern 1: AdminKecamatan class (static methods)            │
│  ├── Rumah::scopeVisibleToCurrentUser()                      │
│  ├── Keluarga::scopeVisibleToCurrentUser()                   │
│  └── Penduduk::scopeVisibleToCurrentUser()                   │
│                                                               │
│  Pattern 2: PanelAccess class (static methods, DIFFERENT)    │
│  ├── DataKesehatanBulanan::scopeVisibleToCurrentUser()       │
│  └── DataPendidikanBulanan::scopeVisibleToCurrentUser()      │
│                                                               │
│  Pattern 3: Inline closure                                     │
│  └── SubmissionBatch::scopeVisibleTo() (takes $user param)   │
│                                                               │
│  Problem: Inconsistent, duplicated logic, maintenance burden  │
└──────────────────────────────────────────────────────────────┘
```

### B. Solution Design

Buat **single `UserScope` class** yang mengimplementasikan semua scope visibility logic:

```php
// app/Support/UserScope.php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserScope
{
    /**
     * Get current authenticated user
     */
    public static function user(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * Get user's kecamatan_id (from user or user's desa)
     */
    public static function getKecamatanId(): ?int
    {
        $user = self::user();

        return $user?->kecamatan_id ?? $user?->desa?->kecamatan_id;
    }

    /**
     * Get user's desa_id
     */
    public static function getDesaId(): ?int
    {
        return self::user()?->desa_id;
    }

    /**
     * Check if user is at kabupaten level (can see all)
     */
    public static function isKabupatenLevel(): bool
    {
        $user = self::user();

        return $user?->hasAnyRole([
            'super_admin', 'admin', 'admin_kominfo',
        ]) ?? false;
    }

    /**
     * Check if user is at kecamatan level
     */
    public static function isKecamatanLevel(): bool
    {
        $user = self::user();

        return $user?->hasRole('admin_kecamatan') ?? false;
    }

    /**
     * Check if user is at desa level
     */
    public static function isDesaLevel(): bool
    {
        $user = self::user();

        return $user?->hasRole('admin_desa') ?? false;
    }

    /**
     * Check if user is at OPD level
     */
    public static function isOpdLevel(): bool
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            'admin_kesehatan', 'operator_kesehatan',
            'admin_pendidikan', 'operator_pendidikan',
            'admin_pu', 'operator_pu',
            'admin_sosial', 'operator_sosial',
            'admin_ekonomi', 'operator_ekonomi',
            'admin_kependudukan', 'operator_kependudukan',
            'admin_lingkungan', 'operator_lingkungan',
            'admin_dinkes', 'operator_dinkes',
            'admin_dispend', 'operator_dispend',
            'admin_dpu', 'operator_dpu',
            'admin_dinsos', 'operator_dinsos',
            'admin_disperindag', 'operator_disperindag',
            'admin_dukcapil', 'operator_dukcapil',
            'admin_dlh', 'operator_dlh',
            // ... other OPD roles
        ]);
    }

    /**
     * Apply visibility scope based on current user role.
     * This is the SINGLE ENTRY POINT for all model scope visibility.
     *
     * Usage in models:
     *   public function scopeVisibleToCurrentUser(Builder $query)
     *   {
     *       return UserScope::visibleTo($query);
     *   }
     */
    public static function visibleTo(Builder $query): Builder
    {
        $user = self::user();

        // Kabupaten level: no restriction
        if (self::isKabupatenLevel()) {
            return $query;
        }

        // Kecamatan level: filter by kecamatan_id
        if (self::isKecamatanLevel()) {
            $kecamatanId = self::getKecamatanId();
            
            // Apply to query based on its table structure
            // Tables with kecamatan_id column directly
            if (self::tableHasColumn($query, 'kecamatan_id')) {
                return $query->where('kecamatan_id', $kecamatanId);
            }
            
            // Tables with through relation (rumah → desa → kecamatan)
            if (self::tableHasRelation($query, 'rumah.desa')) {
                return $query->whereHas('rumah.desa', fn ($q) => 
                    $q->where('kecamatan_id', $kecamatanId)
                );
            }

            return $query;
        }

        // Desa level: filter by desa_id
        if (self::isDesaLevel()) {
            $desaId = self::getDesaId();

            if (self::tableHasColumn($query, 'desa_id')) {
                return $query->where('desa_id', $desaId);
            }

            if (self::tableHasRelation($query, 'rumah')) {
                return $query->whereHas('rumah', fn ($q) => 
                    $q->where('desa_id', $desaId)
                );
            }

            return $query;
        }

        // OPD level: filter by created_by
        if (self::isOpdLevel()) {
            return $query->where('created_by', $user?->id);
        }

        // Fallback: no access
        return $query->whereRaw('1 = 0');
    }

    /**
     * Helper: check if query's table has a column
     */
    private static function tableHasColumn(Builder $query, string $column): bool
    {
        static $cache = [];

        $table = $query->getModel()->getTable();
        $key = "{$table}.{$column}";

        if (! isset($cache[$key])) {
            $cache[$key] = \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }

    /**
     * Helper: check if query's model has a relation
     */
    private static function tableHasRelation(Builder $query, string $relation): bool
    {
        return method_exists($query->getModel(), $relation);
    }
}
```

### C. Model Update Pattern

Setelah `UserScope` dibuat, semua model mengikuti pattern yang sama:

```php
// app/Models/Rumah.php
public function scopeVisibleToCurrentUser(Builder $query): Builder
{
    return UserScope::visibleTo($query);
}

// app/Models/DataKesehatanBulanan.php  
public function scopeVisibleToCurrentUser(Builder $query): Builder
{
    $user = UserScope::user();

    if (UserScope::isKabupatenLevel()) {
        return $query;
    }

    if (UserScope::isKecamatanLevel()) {
        return $query->where('kecamatan_id', UserScope::getKecamatanId());
    }

    if (UserScope::isOpdLevel()) {
        return $query->where('created_by', $user?->id);
    }

    return $query->whereRaw('1 = 0');
}

// app/Models/SubmissionBatch.php
public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
{
    if (! $user || UserScope::forUser($user)->isKabupatenLevel()) {
        return $query;
    }

    if ($user->kecamatan_id) {
        return $query->where('kecamatan_id', $user->kecamatan_id);
    }

    if ($user->desa_id) {
        return $query->whereHas('createdBy', fn (Builder $q) => 
            $q->where('desa_id', $user->desa_id)
        );
    }

    return $query->whereRaw('1 = 0');
}
```

### D. Change Location

| File | Change |
|------|--------|
| `app/Support/UserScope.php` | New class — single scope visibility |
| `app/Models/Rumah.php` | Update scopeVisibleToCurrentUser() → use UserScope |
| `app/Models/Keluarga.php` | Update scopeVisibleToCurrentUser() → use UserScope |
| `app/Models/Penduduk.php` | Update scopeVisibleToCurrentUser() → use UserScope |
| `app/Models/DataKesehatanBulanan.php` | Update scopeVisibleToCurrentUser() → use UserScope |
| `app/Models/DataPendidikanBulanan.php` | Update scopeVisibleToCurrentUser() → use UserScope |
| `app/Models/SubmissionBatch.php` | Keep scopeVisibleTo() with user param (special case) |

### E. Implementation Notes

- `PanelAccess` tetap digunakan untuk **authorization checks** (authorized actions)
- `UserScope` digunakan untuk **data visibility** (what data user can see)
- Keduanya adalah **orthogonal concerns** yang perlu tetap terpisah
- `AdminKecamatan` class di-deprecate → redirect ke `UserScope`

### F. Rollback

1. Revert setiap model ke pattern sebelumnya (AdminKecamatan atau PanelAccess)
2. Delete `UserScope.php`
3. Restore duplikasi logic di setiap model

---

## SPEC-005: Role Constants Refactor

### A. Problem Statement

Role constants tersebar di 3 tempat:

```
┌─────────────────────────────────────────────────────────────┐
│  Lokasi 1: UserRegistration::AVAILABLE_ROLES                │
│  Lokasi 2: RolePermissionSeeder::$roles                     │
│  Lokasi 3: User::canAccessFilament() inline array            │
│                                                              │
│  Problem: Inconsistent, duplicated, hard to maintain        │
└─────────────────────────────────────────────────────────────┘
```

### B. Solution Design

Buat `AppRole` enum:

```php
// app/Enums/AppRole.php

namespace App\Enums;

enum AppRole: string
{
    // Core system roles
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case ADMIN_KOMINFO = 'admin_kominfo';
    case ADMIN_OPD = 'admin_opd';
    case ADMIN_KECAMATAN = 'admin_kecamatan';
    case ADMIN_DESA = 'admin_desa';
    case PUBLIC_ROLE = 'public';

    // OPD Admin roles
    case ADMIN_DINKES = 'admin_dinkes';
    case OPERATOR_DINKES = 'operator_dinkes';
    case ADMIN_DISPEND = 'admin_dispend';
    case OPERATOR_DISPEND = 'operator_dispend';
    case ADMIN_DPU = 'admin_dpu';
    case OPERATOR_DPU = 'operator_dpu';
    case ADMIN_DINSOS = 'admin_dinsos';
    case OPERATOR_DINSOS = 'operator_dinsos';
    case ADMIN_DISPERINDAG = 'admin_disperindag';
    case OPERATOR_DISPERINDAG = 'operator_disperindag';
    case ADMIN_DUKCAPIL = 'admin_dukcapil';
    case OPERATOR_DUKCAPIL = 'operator_dukcapil';
    case ADMIN_DLH = 'admin_dlh';
    case OPERATOR_DLH = 'operator_dlh';
    // ... all other OPD roles

    // Display labels
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Administrator',
            self::ADMIN_KOMINFO => 'Admin Kominfo',
            self::ADMIN_OPD => 'Admin OPD',
            self::ADMIN_KECAMATAN => 'Admin Kecamatan',
            self::ADMIN_DESA => 'Admin Desa',
            self::ADMIN_DINKES => 'Admin Dinkes',
            self::OPERATOR_DINKES => 'Operator Dinkes',
            // ... all labels
            default => ucfirst(str_replace('_', ' ', $this->value)),
        };
    }

    // Which can access Filament
    public function canAccessFilament(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::ADMIN_KOMINFO,
            self::ADMIN_OPD, self::ADMIN_KECAMATAN, self::ADMIN_DESA,
            self::ADMIN_DINKES, self::OPERATOR_DINKES,
            self::ADMIN_DISPEND, self::OPERATOR_DISPEND,
            // ... all OPD admin/operator roles
            => true,
            default => false,
        };
    }

    // Registration available
    public function isAvailableForRegistration(): bool
    {
        return match ($this) {
            self::ADMIN_KECAMATAN, self::ADMIN_DESA,
            self::ADMIN_DINKES, self::OPERATOR_DINKES,
            self::ADMIN_DISPEND, self::OPERATOR_DISPEND,
            // ... all roles that can self-register
            => true,
            default => false,
        };
    }

    // All enum values as array
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

### C. Update Points

```php
// UserRegistration.php — UPDATE
const AVAILABLE_ROLES = AppRole::values();

// User.php — UPDATE
public function canAccessFilament(): bool
{
    if (! $this->role) return false;
    
    return AppRole::tryFrom($this->role)?->canAccessFilament() ?? false;
}

// RolePermissionSeeder.php — UPDATE
$roles = collect(AppRole::cases())
    ->mapWithKeys(fn (AppRole $role) => [$role->value => $role->value])
    ->filter(fn ($name) => ! in_array($name, ['super_admin', 'admin', 'public']))
    ->map(fn ($name) => Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
    ->all();
```

### D. Change Location

| File | Change |
|------|--------|
| `app/Enums/AppRole.php` | New enum class |
| `app/Models/UserRegistration.php` | Use AppRole enum |
| `app/Models/User.php` | Use AppRole enum |
| `database/seeders/RolePermissionSeeder.php` | Use AppRole enum |

### E. Rollback

Hapus enum, revert semua reference ke inline arrays/constants.

---

## SPEC-006: Optimize activeBatchForModule

### A. Problem Statement

```php
// SubmissionBatchService.php lines 496-510 — BEFORE

private function activeBatchForModule(...): ?SubmissionBatch
{
    return SubmissionBatch::query()
        ->where('kecamatan_id', $kecamatanId)
        ->whereIn('status', [STATUS_DRAFT, STATUS_PENDING, STATUS_IN_REVIEW])
        ->with('items')
        ->lockForUpdate()
        ->get()                                      // ← FETCHES ALL
        ->first(fn (SubmissionBatch $batch): bool => // ← FILTERS IN PHP
            $this->batchContainsModule($batch, $module)
            && $this->batchMatchesModulePeriod($batch, $module, $periodeBulan, $semester, $periodeTahun)
        );
}
```

**Problem**: `lockForUpdate()` dengan `get()` mengambil SEMUA batch yang match WHERE, lock seluruh row di database, baru filter di PHP. Untuk kecamatan dengan banyak batch, ini sangat inefficient.

### B. Solution Design

Pindahkan semua filter ke SQL query:

```php
// SubmissionBatchService.php — AFTER

private function activeBatchForModule(
    int $kecamatanId,
    string $module,
    ?int $periodeBulan,
    ?string $semester,
    ?int $periodeTahun,
): ?SubmissionBatch {
    // Build batch query dengan semua filter di SQL
    // Tidak ada lockForUpdate di sini karena activeBatchForModule
    // hanya dipanggil di dalam transaction submit() yang sudah punya lock di level batch creation
    
    $query = SubmissionBatch::query()
        ->where('kecamatan_id', $kecamatanId)
        ->whereIn('status', [
            SubmissionBatch::STATUS_DRAFT,
            SubmissionBatch::STATUS_PENDING,
            SubmissionBatch::STATUS_IN_REVIEW,
        ])
        ->with('items');

    // Filter by module (di dalam WHERE, bukan PHP)
    $query->where(function (Builder $q) use ($module): void {
        $q->where('module', $module)
          ->orWhereRaw('JSON_CONTAINS(package_keys, ?)', [json_encode($module)]);
    });

    // Filter by period — complex karena period ada di payload_snapshot JSON
    // Atau gunakan field langsung jika single-module batch
    $query->where(function (Builder $q) use ($module, $periodeBulan, $semester, $periodeTahun): void {
        // Untuk batch dengan module yang sama
        $q->where(function (Builder $inner) use ($module, $periodeBulan, $semester, $periodeTahun): void {
            $inner->where('module', $module);
            
            if ($module === SubmissionBatch::MODULE_KESEHATAN) {
                $inner->where('periode_bulan', $periodeBulan);
            }
            if ($module === SubmissionBatch::MODULE_PENDIDIKAN) {
                $inner->where('semester', $semester);
            }
            if ($module === SubmissionBatch::MODULE_PENDIDIKAN || $module === SubmissionBatch::MODULE_KESEHATAN) {
                $inner->where('periode_tahun', $periodeTahun);
            }
        });
    });

    $batch = $query->first();

    if (! $batch) {
        return null;
    }

    // Additional validation di PHP untuk mixed batches
    // yang tidak bisa difilter di SQL (package_keys JSON)
    if (! $this->batchMatchesModulePeriod($batch, $module, $periodeBulan, $semester, $periodeTahun)) {
        return null;
    }

    return $batch;
}
```

### C. Alternative (Simpler)

Jika modifikasi terlalu kompleks, cukup gunakan `first()` bukan `get()->first()`:

```php
// Minimum viable fix
private function activeBatchForModule(...): ?SubmissionBatch
{
    // Gunakan first() bukan get()->first() untuk avoid fetching all rows
    return SubmissionBatch::query()
        ->where('kecamatan_id', $kecamatanId)
        ->whereIn('status', [
            SubmissionBatch::STATUS_DRAFT,
            SubmissionBatch::STATUS_PENDING,
            SubmissionBatch::STATUS_IN_REVIEW,
        ])
        ->with('items')
        ->first(fn (SubmissionBatch $batch): bool =>
            $this->batchContainsModule($batch, $module)
            && $this->batchMatchesModulePeriod($batch, $module, $periodeBulan, $semester, $periodeTahun)
        );
}
```

**Catatan**: `first(callback)` di Laravel akan iterate setiap record sampai menemukan match, tidak fetch semua ke memory. Ini sudah cukup untuk performance improvement tanpa refactor besar.

### D. Change Location

| File | Line | Change |
|------|------|--------|
| `app/Services/SubmissionBatchService.php` | 496-510 | Replace `get()->first()` dengan `first()` |

### E. Rollback

Revert ke `get()->first()` pattern.

---

## SPEC-007: Mobile Improvements

### A. Duplicate role_label Key

```php
// MobileAuthController.php lines 50-68 — BEFORE

private function profile(User $user): array
{
    // ...
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role_label,
        'role_label' => $user->role_label,   // ← DUPLICATE
        'role_label' => $user->role_label,   // ← DUPLICATE (was line 60)
        'roles' => $user->roles->pluck('name')->values()->all(),
        // ...
    ];
}

// AFTER

private function profile(User $user): array
{
    $user->loadMissing('roles', 'kecamatan', 'desa', 'opd');

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->roles->first()?->name,
        'role_label' => $user->role_label,
        'roles' => $user->roles->pluck('name')->values()->all(),
        'kecamatan_id' => $user->kecamatan_id,
        'kecamatan' => $user->kecamatan?->nama,
        'desa_id' => $user->desa_id,
        'desa' => $user->desa?->nama,
    ];
}
```

### B. Mobile Rate Limiting

```php
// routes/api.php — ADD throttle untuk sync endpoints

Route::prefix('v1/mobile')->group(function (): void {
    Route::post('login', [MobileAuthController::class, 'login'])
        ->middleware('throttle:api');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [MobileAuthController::class, 'me']);
        Route::post('logout', [MobileAuthController::class, 'logout']);
        Route::get('bootstrap', MobileBootstrapController::class);
        
        // NEW: Add rate limiting for sync operations
        Route::post('sync/push', [MobileSyncController::class, 'push'])
            ->middleware('throttle:mobile-sync');
        Route::get('sync/pull', [MobileSyncController::class, 'pull'])
            ->middleware('throttle:mobile-sync');
    });
});

// app/Providers/RouteServiceProvider.php — ADD throttle configuration

protected function configureRateLimiting(): void
{
    // ... existing code ...

    RateLimiter::for('mobile-sync', function (Request $request) {
        return Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip())
            ->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Terlalu banyak request sync. Silakan tunggu sebentar.',
                ], 429, $headers);
            });
    });
}
```

---

## SPEC-008: AdminDesa Rejection Notification

### A. Problem Statement

Saat AdminKominfo menolak batch, `notifyKecamatan()` mengirim notifikasi ke `admin_kecamatan`. Tapi jika batch dibuat oleh `admin_desa`, AdminDesa tidak dikabari — mereka tidak tahu datanya ditolak.

### B. Solution Design

Tambahkan method `notifyCreator()` di `BatchApprovalService`:

```php
// BatchApprovalService.php — ADD new method

/**
 * Notify batch creator (AdminDesa or AdminKecamatan) about status change
 */
private function notifyCreator(SubmissionBatch $batch, string $status, User $actor): void
{
    $creator = $batch->createdBy;

    if (! $creator) {
        return;
    }

    // Don't notify if creator is the one who performed the action
    if ((int) $creator->id === (int) $actor->id) {
        return;
    }

    $statusLabel = match ($status) {
        SubmissionBatch::STATUS_APPROVED => 'disetujui',
        SubmissionBatch::STATUS_REJECTED => 'ditolak',
        SubmissionBatch::STATUS_REVISION => 'diminta untuk direvisi',
        default => 'diperbarui',
    };

    NotificationFacade::sendNow(
        $creator,
        new BatchNotification(
            "Batch {$batch->submission_number} {$statusLabel}",
            sprintf(
                'Batch %s (%s) untuk %s telah %s oleh %s.',
                $batch->submission_number,
                SubmissionBatch::moduleLabel($batch->module),
                $batch->kecamatan?->nama ?? '-',
                $statusLabel,
                $actor->name,
            ),
            class_basename($batch),
            $batch->getKey(),
            $actor->id,
            $actor->name,
        ),
    );
}

// UPDATE: call notifyCreator in transition() when rejecting

if ($notify && $notificationType !== null) {
    DB::afterCommit(function () use ($updatedBatch, $actor, $notificationType): void {
        // ... existing notification dispatch ...

        // For reject/revise: also notify the creator (AdminDesa)
        if (in_array($notificationType, ['reject', 'revision'])) {
            // notifyCreator will check if creator != actor
            $this->notifyCreator($updatedBatch, $updatedBatch->status, $actor);
        }
    });
}
```

### C. Change Location

| File | Line | Change |
|------|------|--------|
| `app/Services/BatchApprovalService.php` | +20 lines | Add `notifyCreator()` method + call it |

### D. Rollback

Hapus `notifyCreator()` method dan call-nya.

---

## Migrations & Database Changes

### Summary of All Migrations Needed

| # | File | Description | Risk |
|---|------|-------------|------|
| M1 | `2026_08_08_000001_add_submission_batch_fk_to_opd_tables.php` | Add `submission_batch_id` FK to kesehatan & pendidikan | **MEDIUM** — new FK column |
| M2 | `2026_08_08_000002_add_indexes_for_scope_visibility.php` | Add composite indexes for faster scoping | LOW — add only |
| M3 | `2026_08_08_000003_add_nik_unique_per_kecamatan.php` | Add unique constraint (optional, Opsi B) | **MEDIUM** — unique constraint |

### M1: OPD Batch FK

```php
<?php
// database/migrations/2026_08_08_000001_add_submission_batch_fk_to_opd_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data Kesehatan Bulanan
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->foreignId('submission_batch_id')
                ->nullable()
                ->after('kecamatan_id')
                ->constrained('submission_batches')
                ->nullOnDelete();

            $table->index([
                'submission_batch_id',
                'kecamatan_id',
                'periode_bulan',
                'periode_tahun',
            ], 'data_kesehatan_scope_idx');
        });

        // Data Pendidikan Bulanan
        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->foreignId('submission_batch_id')
                ->nullable()
                ->after('kecamatan_id')
                ->constrained('submission_batches')
                ->nullOnDelete();

            $table->index([
                'submission_batch_id',
                'kecamatan_id',
                'semester',
                'periode_tahun',
            ], 'data_pendidikan_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->dropIndex('data_kesehatan_scope_idx');
            $table->dropForeign(['submission_batch_id']);
            $table->dropColumn('submission_batch_id');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->dropIndex('data_pendidikan_scope_idx');
            $table->dropForeign(['submission_batch_id']);
            $table->dropColumn('submission_batch_id');
        });
    }
};
```

### M2: Performance Indexes

```php
<?php
// database/migrations/2026_08_08_000002_add_indexes_for_scope_visibility.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index for inApprovedBatch scope
        Schema::table('submission_batch_items', function (Blueprint $table) {
            $table->index(['entity_type', 'entity_id'], 'sbi_entity_lookup_idx');
            $table->index(['submission_batch_id', 'package_key'], 'sbi_batch_module_idx');
        });

        // Composite index for rumah scope
        Schema::table('rumah', function (Blueprint $table) {
            $table->index(['desa_id', 'kode_rumah'], 'rumah_desa_kode_idx');
        });

        // Composite index for keluarga scope
        Schema::table('keluarga', function (Blueprint $table) {
            $table->index(['rumah_id', 'nomor_kk'], 'keluarga_rumah_kk_idx');
        });
    }

    public function down(): void
    {
        Schema::table('submission_batch_items', function (Blueprint $table) {
            $table->dropIndex('sbi_entity_lookup_idx');
            $table->dropIndex('sbi_batch_module_idx');
        });

        Schema::table('rumah', function (Blueprint $table) {
            $table->dropIndex('rumah_desa_kode_idx');
        });

        Schema::table('keluarga', function (Blueprint $table) {
            $table->dropIndex('keluarga_rumah_kk_idx');
        });
    }
};
```

---

## Testing Strategy

### Test Priority Matrix

| Spec | Spec # | Tests Needed | Priority | Effort |
|------|--------|-------------|----------|--------|
| Notification Fix | SPEC-001 | 2 tests | HIGH | 1 jam |
| NIK Uniqueness | SPEC-002 | 2 tests | HIGH | 1 jam |
| OPD Integration | SPEC-003 | 3 tests | HIGH | 2 jam |
| Scope Consolidation | SPEC-004 | 4 tests | MEDIUM | 2 jam |
| Role Refactor | SPEC-005 | 1 test | MEDIUM | 1 jam |
| Batch Optimization | SPEC-006 | 0 (refactor only) | LOW | - |
| Mobile Fixes | SPEC-007 | 1 test | LOW | 30 min |
| AdminDesa Notification | SPEC-008 | 1 test | LOW | 30 min |

### Test Files to Update/Create

```
tests/
├── Feature/
│   ├── SubmissionBatchNotificationTest.php     [NEW] — SPEC-001, SPEC-008
│   ├── MobileSyncNikUniquenessTest.php       [NEW] — SPEC-002
│   ├── SubmissionBatchOpdIntegrationTest.php  [NEW] — SPEC-003
│   └── UserScopeVisibilityTest.php            [NEW] — SPEC-004
│   └── AppRoleEnumTest.php                    [NEW] — SPEC-005
│   └── SubmissionBatchTest.php                [UPDATE] — add SPEC-001, SPEC-008
│   └── MobileSyncTest.php                     [UPDATE] — add SPEC-002
└── Unit/
    ├── SubmissionBatchServiceTest.php          [NEW] — SPEC-006
    └── UserScopeTest.php                       [NEW] — SPEC-004
```

### Test Coverage Target

```
Before:
  - SubmissionBatchTest: 7 tests
  - Coverage: ~60% (core workflow only)

After:
  - SubmissionBatchNotificationTest: 2 tests (notification dispatch)
  - MobileSyncNikUniquenessTest: 2 tests (NIK validation)
  - SubmissionBatchOpdIntegrationTest: 3 tests (OPD workflow)
  - UserScopeVisibilityTest: 4 tests (scope consolidation)
  - AppRoleEnumTest: 1 test (role enum)
  - Coverage target: ~80%
```

---

## Rollback Plan

### Per-Spec Rollback

| Spec | Rollback Steps | Rollback Time |
|------|---------------|---------------|
| SPEC-001 | Revert `transition()` notification block ke nested if | < 5 menit |
| SPEC-002 | Hapus unique check di `storePenduduk()` | < 5 menit |
| SPEC-003 | Rollback migration M1 (3 drop operations) | < 10 menit |
| SPEC-004 | Revert semua model ke pattern sebelumnya | < 30 menit |
| SPEC-005 | Hapus enum, revert semua reference | < 20 menit |
| SPEC-006 | Revert `first()` ke `get()->first()` | < 2 menit |
| SPEC-007 | Hapus throttle config + fix duplicate key | < 5 menit |
| SPEC-008 | Hapus `notifyCreator()` method dan call | < 5 menit |

### Full Rollback Procedure

```bash
# If critical issue found after deployment:
php artisan migrate:rollback --step=3

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Clear any generated code
composer dump-autoload

# Verify
php artisan test
```

---

## Implementation Roadmap

### Sprint 1: Critical Fixes (Week 1)

```
Day 1-2: SPEC-001 (Notification fix)
  └── Effort: < 1 hari
  └── Impact: Workflow Kominfo sekarang berfungsi

Day 3-4: SPEC-002 (NIK uniqueness)
  └── Effort: < 1 hari
  └── Impact: Data integrity terjaga

Day 5: SPEC-007a (Mobile duplicate key fix)
  └── Effort: < 30 menit
  └── Impact: Clean API response
```

### Sprint 2: OPD Integration (Week 2-3)

```
Day 1-2: SPEC-003 Migration + Model update
  └── Effort: 1-2 hari
  └── Impact: OPD data masuk workflow

Day 3-4: SPEC-003 Service + Filament update
  └── Effort: 1-2 hari
  └── Impact: Full OPD workflow end-to-end

Day 5: SPEC-003 Testing
  └── Effort: 1 hari
  └── Impact: Regression coverage
```

### Sprint 3: Refactoring (Week 4)

```
Day 1-2: SPEC-004 (Scope consolidation)
  └── Effort: 2 hari
  └── Impact: Consistent code, easier maintenance

Day 3: SPEC-005 (Role enum)
  └── Effort: 1 hari
  └── Impact: Centralized role definitions

Day 4: SPEC-006 (Batch optimization)
  └── Effort: < 1 hari
  └── Impact: Performance improvement

Day 5: SPEC-007b (Rate limiting) + SPEC-008 (AdminDesa notification)
  └── Effort: 1 hari
  └── Impact: Security hardening + UX improvement
```

### Sprint 4: Testing & Polish (Week 5)

```
Day 1-3: Write all missing tests
Day 4: Integration testing
Day 5: Code review + documentation update
```

---

## Decision Summary

### Keep As Is ✅

| Item | Reason |
|------|--------|
| Laravel + Filament | Stack proven, no reason to change |
| MySQL | Works, no PostgreSQL-specific needs |
| Spatie Permission RBAC | Pattern correct |
| JSON snapshot storage | Works well |
| Database notifications only | Sufficient for use case |
| sync queue driver | Acceptable for current scale |
| Single monolith | No microservices need |
| Mobile direct write pattern | Design decision — acceptable |

### Fix Now ⚠️

| Item | Effort | Status |
|------|--------|--------|
| SPEC-001: Notification dead code | < 1 hari | TODO |
| SPEC-002: NIK uniqueness | < 1 hari | TODO |
| SPEC-007a: Mobile duplicate key | < 30 menit | TODO |

### Fix Later 🔧

| Item | Effort | Sprint |
|------|--------|--------|
| SPEC-003: OPD batch integration | 2-4 hari | Sprint 2 |
| SPEC-004: Scope consolidation | 2-3 hari | Sprint 3 |
| SPEC-005: Role enum refactor | 1-2 hari | Sprint 3 |
| SPEC-006: Batch optimization | < 1 hari | Sprint 3 |
| SPEC-007b: Rate limiting | < 1 hari | Sprint 3 |
| SPEC-008: AdminDesa notification | < 1 hari | Sprint 3 |

### Refactor 🔄

| Item | Target |
|------|--------|
| SPEC-004: Scope consolidation | Single `UserScope` class |
| SPEC-005: Role constants | `AppRole` enum |

---

## Non-Goals (Explicitly Excluded)

Berdasarkan aturan user, dokumen ini **TIDAK** mencakup:

- ❌ Mengganti Laravel/Filament dengan framework lain
- ❌ Membuat microservices tanpa kebutuhan
- ❌ Membuat abstraction berlebihan
- ❌ Mengganti MySQL dengan PostgreSQL tanpa alasan kuat
- ❌ Memindahkan semua file hanya untuk restructure folder
- ❌ Membuat workflow baru jika workflow existing sudah benar
- ❌ Mengubah architecture hanya karena terlihat lebih modern

**Prioritas dokumen ini:**
1. ✅ CORRECTNESS
2. ✅ SECURITY
3. ✅ DATA INTEGRITY
4. ✅ WORKFLOW INTEGRITY
5. ✅ AUTHORIZATION
6. ✅ AUDITABILITY
7. ✅ TESTABILITY
8. ✅ MAINTAINABILITY
9. ✅ PERFORMANCE
10. ✅ SCALABILITY

---

*Dokumen ini adalah blueprint final untuk implementasi perbaikan SIPANDU. Semua spec dirancang agar: small, measurable, dan testable. Tidak ada perubahan arsitektur besar — hanya perbaikan targeted yang fokus pada correctness, security, dan data integrity.*