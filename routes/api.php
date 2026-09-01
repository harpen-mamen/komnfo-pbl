<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileBootstrapController;
use App\Http\Controllers\Api\MobileDataController;
use App\Http\Controllers\Api\MobileSyncController;
use App\Http\Controllers\Api\PublicStatsController;
use App\Models\Desa;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PublicPageController;
use App\Http\Controllers\Api\TentangDaerahController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\PublicStatisticsController;
use App\Http\Controllers\Api\PublicMapController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| PUBLIC API v1: Arsitektur §12
| Only reads from APPROVED batches (Arsitektur §4.3)
| No authentication required.
|
*/

// PUBLIC API v1 - Read-only statistics (Arsitektur §12.1)
Route::prefix('v1')->group(function (): void {
    // Statistics (existing)
    Route::get('/stats', [PublicStatsController::class, 'stats'])->name('api.v1.stats');
    Route::get('/penduduk', [PublicStatsController::class, 'penduduk'])->name('api.v1.penduduk');
    Route::get('/kecamatan', [PublicStatsController::class, 'kecamatan'])->name('api.v1.kecamatan');
    Route::get('/desa', [PublicStatsController::class, 'desa'])->name('api.v1.desa');
    Route::get('/kecamatan/{id}/desa', [PublicStatsController::class, 'desaByKecamatan'])
        ->name('api.v1.kecamatan.desa');

    // Public portal endpoints (consumed by Next.js)
    Route::prefix('public')->group(function (): void {
        Route::get('/hero',         [PublicPageController::class, 'hero'])->name('api.v1.public.hero');
        Route::get('/home',         [PublicPageController::class, 'home'])->name('api.v1.public.home');
        Route::get('/berita',       [PublicNewsController::class, 'index'])->name('api.v1.public.berita');
        Route::get('/berita/{slug}',[PublicNewsController::class, 'show'])->name('api.v1.public.berita.show');
        Route::get('/statistik',    [PublicStatisticsController::class, 'index'])->name('api.v1.public.statistik');
        Route::get('/peta',         [PublicMapController::class, 'index'])->name('api.v1.public.peta');
        Route::get('/peta/cari',    [PublicMapController::class, 'search'])
            ->middleware('throttle:public-map-search')
            ->name('api.v1.public.peta.cari');
        Route::get('/layanan',      [PublicPageController::class, 'layanan'])->name('api.v1.public.layanan');

        // TENTANG DAERAH (Kabupaten Kepulauan Sangihe)
        Route::prefix('tentang-daerah')->name('api.v1.public.tentang.')->group(function (): void {
            Route::get('/',                       [TentangDaerahController::class, 'index'])->name('index');
            Route::get('/sambutan-bupati',        [TentangDaerahController::class, 'sambutan'])->name('sambutan');
            Route::get('/pejabat',                [TentangDaerahController::class, 'pejabat'])->name('pejabat');
            Route::get('/pejabat/{id}',           [TentangDaerahController::class, 'pejabatDetail'])->name('pejabat.show');
            Route::get('/opd',                    [TentangDaerahController::class, 'opd'])->name('opd');
            Route::get('/opd/{slug}',             [TentangDaerahController::class, 'opdDetail'])->name('opd.show');
            Route::get('/sejarah',                [TentangDaerahController::class, 'sejarah'])->name('sejarah');
            Route::get('/visi-misi',              [TentangDaerahController::class, 'visiMisi'])->name('visi-misi');
            Route::get('/geografi',               [TentangDaerahController::class, 'geografi'])->name('geografi');
            Route::get('/lambang',                [TentangDaerahController::class, 'lambang'])->name('lambang');
        });
    });

    // Registration form (public)
    Route::prefix('registration')->name('api.v1.registration.')->group(function (): void {
        Route::get('/form-data', [RegistrationController::class, 'formData'])->name('form-data');
        Route::post('/',         [RegistrationController::class, 'register'])->name('submit');
    });
});

// Admin actions (PDF/Excel export) consumed by Filament panel via fetch
Route::middleware('auth:sanctum')->prefix('v1/admin')->name('api.v1.admin.')->group(function (): void {
    Route::get('/laporan/rumah/pdf',                 [LaporanController::class, 'rumahPdf'])->name('laporan.rumah.pdf');
    Route::get('/laporan/rumah/excel',               [LaporanController::class, 'rumahExcel'])->name('laporan.rumah.excel');
    Route::get('/laporan/keluarga/pdf',             [LaporanController::class, 'keluargaPdf'])->name('laporan.keluarga.pdf');
    Route::get('/laporan/keluarga/excel',           [LaporanController::class, 'keluargaExcel'])->name('laporan.keluarga.excel');
    Route::get('/laporan/penduduk/pdf',             [LaporanController::class, 'pendudukPdf'])->name('laporan.penduduk.pdf');
    Route::get('/laporan/penduduk/excel',           [LaporanController::class, 'pendudukExcel'])->name('laporan.penduduk.excel');
    Route::get('/laporan/pendidikan-bulanan/pdf',   [LaporanController::class, 'pendidikanBulananPdf'])->name('laporan.pendidikan.pdf');
    Route::get('/laporan/pendidikan-bulanan/excel', [LaporanController::class, 'pendidikanBulananExcel'])->name('laporan.pendidikan.excel');
    Route::get('/laporan/kesehatan-bulanan/pdf',    [LaporanController::class, 'kesehatanBulananPdf'])->name('laporan.kesehatan.pdf');
    Route::get('/laporan/kesehatan-bulanan/excel',  [LaporanController::class, 'kesehatanBulananExcel'])->name('laporan.kesehatan.excel');
});

// Authenticated routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Get desa by kecamatan
Route::get('/desa/by-kecamatan/{kecamatanId}', function ($kecamatanId) {
    return Desa::where('kecamatan_id', $kecamatanId)
        ->orderBy('nama')
        ->get(['id', 'nama']);
});

// SPRINT 6: Mobile API v1 - Enhanced routes for Flutter app
Route::prefix('v1/mobile')->group(function (): void {
    Route::post('login', [MobileAuthController::class, 'login'])->middleware('throttle:api');

    Route::middleware('auth:sanctum')->group(function (): void {
        // Auth
        Route::get('me', [MobileAuthController::class, 'me']);
        Route::post('logout', [MobileAuthController::class, 'logout']);

        // Bootstrap for initial data load
        Route::get('bootstrap', MobileBootstrapController::class);

        // Dashboard summary
        Route::get('dashboard', [MobileDataController::class, 'dashboard']);

        // Data CRUD - Rumah
        Route::get('rumah', [MobileDataController::class, 'rumah']);
        Route::get('rumah/{id}', [MobileDataController::class, 'rumahDetail'])->where('id', '[0-9]+');

        // Data CRUD - Keluarga
        Route::get('keluarga', [MobileDataController::class, 'keluarga']);
        Route::get('keluarga/{id}', [MobileDataController::class, 'keluargaDetail'])->where('id', '[0-9]+');

        // Data CRUD - Penduduk
        Route::get('penduduk', [MobileDataController::class, 'penduduk']);
        Route::get('penduduk/{id}', [MobileDataController::class, 'pendudukDetail'])->where('id', '[0-9]+');

        // Reference data for forms
        Route::get('reference', [MobileDataController::class, 'reference']);

        // Batches
        Route::get('batches', [MobileDataController::class, 'batches']);

        // SPEC-007b: A11 - rate-limited sync endpoints (30 req/min per user).
        Route::post('sync/push', [MobileSyncController::class, 'push'])
            ->middleware('throttle:mobile-sync');
        Route::get('sync/pull', [MobileSyncController::class, 'pull'])
            ->middleware('throttle:mobile-sync');
        Route::get('sync/batch-statuses', [MobileSyncController::class, 'batchStatuses'])
            ->middleware('throttle:mobile-sync');
    });
});
