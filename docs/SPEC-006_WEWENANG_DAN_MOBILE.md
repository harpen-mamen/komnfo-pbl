# SPEC-006: Wewenang & Mobile Foundation

## Overview

Sprint 6 ini bertujuan untuk memperkuat fondasi sistem dengan:
1. Sistem wewenang (authorization) terpusat
2. Service notifikasi yang komprehensif
3. Fondasi API untuk Flutter mobile app
4. Kontrol input data berdasarkan wewenang

## Architecture

### 1. Authorization System

#### WewenangService (App\Services\Authorization\WewenangService)
Service utama untuk authorization yang menyediakan:
- Level checks (Kabupaten, Kecamatan, Desa, OPD)
- Module access control
- Region-based access control
- CRUD authorization per model

#### Key Methods:
`php
// Level checks
WewenangService::isKabupatenLevel() // Super Admin, Admin, Admin Kominfo
WewenangService::isKecamatanLevel() // Admin Kecamatan
WewenangService::isDesaLevel() // Admin Desa
WewenangService::isOpdLevel() // Admin/Operator OPD

// Module access
WewenangService::canAccessModule(string ): bool
WewenangService::getAccessibleModules(): array

// Region access
WewenangService::canCreateInRegion(int , ?int  = null): bool
WewenangService::getAccessibleKecamatanIds(): array
WewenangService::getAccessibleDesaIds(): array

// CRUD
WewenangService::canEditRecord(string , int ): bool
WewenangService::canDeleteRecord(string , int ): bool

// Submission
WewenangService::canSubmitData(): bool
WewenangService::canVerifyData(): bool
WewenangService::canApproveData(): bool
WewenangService::canRejectData(): bool
`

#### Module Constants:
`php
WewenangService::MODULE_RUMAH = 'rumah'
WewenangService::MODULE_KELUARGA = 'keluarga'
WewenangService::MODULE_PENDUDUK = 'penduduk'
WewenangService::MODULE_KESEHATAN = 'kesehatan'
WewenangService::MODULE_PENDIDIKAN = 'pendidikan'
WewenangService::MODULE_PUSKESMAS = 'puskesmas'
WewenangService::MODULE_SEKOLAH = 'sekolah'
`

### 2. Notification System

#### NotificationService (App\Services\Notification\NotificationService)
Service untuk semua notifikasi di sistem:
- Batch baru dari Admin Desa
- Batch menunggu verifikasi
- Status perubahan batch
- Persetujuan/penolakan/revisi

#### Key Methods:
`php
// Batch notifications
NotificationService::notifyKecamatanBatchBaru(SubmissionBatch , User ): void
NotificationService::notifyKominfoBatchMenunggu(SubmissionBatch , User ): void
NotificationService::notifyCreatorBatchStatus(SubmissionBatch , string , User ): void
NotificationService::notifyBatchApproved(SubmissionBatch , User ): void
NotificationService::notifyBatchRejected(SubmissionBatch , User , ?string  = null): void
NotificationService::notifyBatchRevision(SubmissionBatch , User , ?string  = null): void

// Mobile notifications
NotificationService::notifyMobileSubmissionStatus(int , SubmissionBatch , string ): void
`

### 3. Data Input Service

#### DataInputService (App\Services\DataInput\DataInputService)
Service untuk validasi dan kontrol input data:
- Validasi per model
- Authorization assertions
- Data integrity checks

#### Key Methods:
`php
// CRUD operations
DataInputService::storeRumah(array ): Rumah
DataInputService::updateRumah(Rumah , array ): Rumah
DataInputService::deleteRumah(Rumah ): bool

DataInputService::storeKeluarga(array ): Keluarga
DataInputService::updateKeluarga(Keluarga , array ): Keluarga
DataInputService::deleteKeluarga(Keluarga ): bool

DataInputService::storePenduduk(array ): Penduduk
DataInputService::updatePenduduk(Penduduk , array ): Penduduk
DataInputService::deletePenduduk(Penduduk ): bool

// Validations
DataInputService::validateRumah(array , ?int  = null): array
DataInputService::validateKeluarga(array , ?int  = null): array
DataInputService::validatePenduduk(array , ?int  = null): array

// Authorization assertions
DataInputService::assertCanCreateRumah(array ): void
DataInputService::assertCanEditRumah(Rumah ): void
DataInputService::assertCanDeleteRumah(Rumah ): void
// ... same pattern for Keluarga and Penduduk
`

### 4. Mobile API Foundation

#### Mobile API Routes (outes/api.php)
`php
Route::prefix('v1/mobile')->group(function () {
    // Auth
    Route::post('login', [MobileAuthController::class, 'login']);
    Route::get('me', [MobileAuthController::class, 'me']);
    Route::post('logout', [MobileAuthController::class, 'logout']);

    // Bootstrap
    Route::get('bootstrap', MobileBootstrapController::class);

    // Dashboard
    Route::get('dashboard', [MobileDataController::class, 'dashboard']);

    // CRUD - Rumah
    Route::get('rumah', [MobileDataController::class, 'rumah']);
    Route::get('rumah/{id}', [MobileDataController::class, 'rumahDetail']);

    // CRUD - Keluarga
    Route::get('keluarga', [MobileDataController::class, 'keluarga']);
    Route::get('keluarga/{id}', [MobileDataController::class, 'keluargaDetail']);

    // CRUD - Penduduk
    Route::get('penduduk', [MobileDataController::class, 'penduduk']);
    Route::get('penduduk/{id}', [MobileDataController::class, 'pendudukDetail']);

    // Reference
    Route::get('reference', [MobileDataController::class, 'reference']);

    // Sync
    Route::post('sync/push', [MobileSyncController::class, 'push']);
    Route::get('sync/pull', [MobileSyncController::class, 'pull']);
    Route::get('sync/batch-statuses', [MobileSyncController::class, 'batchStatuses']);
});
`

#### API Resources
`php
App\Http\Resources\RumahResource
App\Http\Resources\KeluargaResource
App\Http\Resources\PendudukResource
App\Http\Resources\UserResource
App\Http\Resources\SubmissionBatchResource
`

## Role-Based Module Access

| Role | Rumah | Keluarga | Penduduk | Kesehatan | Pendidikan |
|------|-------|----------|----------|-----------|-----------|
| Super Admin | ? | ? | ? | ? | ? |
| Admin | ? | ? | ? | ? | ? |
| Admin Kominfo | ? | ? | ? | ? | ? |
| Admin Kecamatan | ? | ? | ? | ? | ? |
| Admin Desa | ? | ? | ? | ? | ? |
| Admin Kesehatan | ? | ? | ? | ? | ? |
| Operator Kesehatan | ? | ? | ? | ? | ? |
| Admin Pendidikan | ? | ? | ? | ? | ? |
| Operator Pendidikan | ? | ? | ? | ? | ? |

## Submission Workflow

`
Admin Desa ? [Submit] ? Draft
                       ?
Admin Kecamatan ? [Review] ? Pending
                                 ?
                         Admin Kominfo ? [Verify]
                                            ?
                               +-------------------------+
                               ?                         ?
                          Approved                   Rejected
                               ?                         ?
                          Published                  Archived
`

## Data Visibility

- **Kabupaten Level**: See all data in all regions
- **Kecamatan Level**: See data in their kecamatan only
- **Desa Level**: See data in their desa only
- **OPD Level**: See data created by themselves only

## Files Created

### Services
- pp/Contracts/WewenangInterface.php
- pp/Services/Authorization/WewenangService.php
- pp/Services/Notification/NotificationService.php
- pp/Services/DataInput/DataInputService.php

### API Resources
- pp/Http/Resources/RumahResource.php
- pp/Http/Resources/KeluargaResource.php
- pp/Http/Resources/PendudukResource.php
- pp/Http/Resources/UserResource.php
- pp/Http/Resources/SubmissionBatchResource.php

### Controllers
- pp/Http/Controllers/Api/MobileDataController.php

### Documentation
- docs/SPEC-006_WEWENANG_DAN_MOBILE.md

## Next Steps

1. **Flutter App**: Create complete Flutter app using these APIs
2. **Push Data**: Implement create/update/delete operations via API
3. **Real-time**: Add WebSocket for real-time notifications
4. **Email**: Add email channel for notifications
5. **Policy Classes**: Create Laravel Policy classes for models
