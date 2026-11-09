<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileBootstrapController;
use App\Http\Controllers\Api\MobileDataController;
use App\Http\Controllers\Api\MobileMasterDataController;
use App\Http\Controllers\Api\MobileFieldController;
use App\Http\Controllers\Api\PublicStatsController;
use App\Models\Wilayah;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PublicPageController;
use App\Http\Controllers\Api\TentangDaerahController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\PublicStatisticsController;
use App\Http\Controllers\Api\PublicMapController;
use App\Http\Controllers\Api\PublicInfografisController;

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
        Route::get('/hero', [PublicPageController::class, 'hero'])->name('api.v1.public.hero');
        Route::get('/home', [PublicPageController::class, 'home'])->name('api.v1.public.home');
        Route::get('/berita', [PublicNewsController::class, 'index'])->name('api.v1.public.berita');
        Route::get('/berita/{slug}', [PublicNewsController::class, 'show'])->name('api.v1.public.berita.show');
        Route::get('/statistik', [PublicStatisticsController::class, 'index'])->name('api.v1.public.statistik');
        Route::get('/peta', [PublicMapController::class, 'index'])->name('api.v1.public.peta');
        Route::get('/cuaca', [\App\Http\Controllers\Api\PublicWeatherController::class, 'index'])->name('api.v1.public.cuaca');
        Route::get('/peta/cari', [PublicMapController::class, 'search'])
            ->middleware('throttle:public-map-search')
            ->name('api.v1.public.peta.cari');
        Route::get('/layanan', [PublicPageController::class, 'layanan'])->name('api.v1.public.layanan');
        Route::get('/infografis', [PublicInfografisController::class, 'index'])->name('api.v1.public.infografis');
        Route::get('/infografis/categories', [PublicInfografisController::class, 'categories'])->name('api.v1.public.infografis.categories');
        Route::get('/infografis/{slug}', [PublicInfografisController::class, 'show'])->name('api.v1.public.infografis.show');

        // TENTANG DAERAH (Kabupaten Kepulauan Sangihe)
        Route::prefix('tentang-daerah')->name('api.v1.public.tentang.')->group(function (): void {
            Route::get('/', [TentangDaerahController::class, 'index'])->name('index');
            Route::get('/sambutan-bupati', [TentangDaerahController::class, 'sambutan'])->name('sambutan');
            Route::get('/pejabat', [TentangDaerahController::class, 'pejabat'])->name('pejabat');
            Route::get('/pejabat/{id}', [TentangDaerahController::class, 'pejabatDetail'])->name('pejabat.show');
            Route::get('/opd', [TentangDaerahController::class, 'opd'])->name('opd');
            Route::get('/opd/{slug}', [TentangDaerahController::class, 'opdDetail'])->name('opd.show');
            Route::get('/sejarah', [TentangDaerahController::class, 'sejarah'])->name('sejarah');
            Route::get('/visi-misi', [TentangDaerahController::class, 'visiMisi'])->name('visi-misi');
            Route::get('/geografi', [TentangDaerahController::class, 'geografi'])->name('geografi');
            Route::get('/lambang', [TentangDaerahController::class, 'lambang'])->name('lambang');
        });
    });

    // Registration form (public)
    Route::prefix('registration')->name('api.v1.registration.')->group(function (): void {
        Route::get('/form-data', [RegistrationController::class, 'formData'])->name('form-data');
        Route::post('/', [RegistrationController::class, 'register'])->name('submit');
    });
});

// Admin actions (PDF/Excel export) consumed by Filament panel via fetch
Route::middleware(['auth:sanctum', \App\Http\Middleware\BupatiDashboardOnly::class])->prefix('v1/admin')->name('api.v1.admin.')->group(function (): void {
    Route::get('/laporan/rumah/pdf', [LaporanController::class, 'rumahPdf'])->name('laporan.rumah.pdf');
    Route::get('/laporan/rumah/excel', [LaporanController::class, 'rumahExcel'])->name('laporan.rumah.excel');
    Route::get('/laporan/keluarga/pdf', [LaporanController::class, 'keluargaPdf'])->name('laporan.keluarga.pdf');
    Route::get('/laporan/keluarga/excel', [LaporanController::class, 'keluargaExcel'])->name('laporan.keluarga.excel');
    Route::get('/laporan/penduduk/pdf', [LaporanController::class, 'pendudukPdf'])->name('laporan.penduduk.pdf');
    Route::get('/laporan/penduduk/excel', [LaporanController::class, 'pendudukExcel'])->name('laporan.penduduk.excel');
    Route::get('/laporan/pendidikan-bulanan/pdf', [LaporanController::class, 'pendidikanBulananPdf'])->name('laporan.pendidikan.pdf');
    Route::get('/laporan/pendidikan-bulanan/excel', [LaporanController::class, 'pendidikanBulananExcel'])->name('laporan.pendidikan.excel');
    Route::get('/laporan/kesehatan-bulanan/pdf', [LaporanController::class, 'kesehatanBulananPdf'])->name('laporan.kesehatan.pdf');
    Route::get('/laporan/kesehatan-bulanan/excel', [LaporanController::class, 'kesehatanBulananExcel'])->name('laporan.kesehatan.excel');
});

// Authenticated routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Get desa by kecamatan
Route::get('/desa/by-kecamatan/{kecamatanId}', function ($kecamatanId) {
    return Wilayah::where('parent_id', $kecamatanId)
        ->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])
        ->where('aktif', true)
        ->orderBy('nama')
        ->get(['id', 'nama']);
});

// SPRINT 6: Mobile API v1 - Enhanced routes for Flutter app
Route::prefix('v1/mobile')->group(function (): void {
    // Login email + password (untuk admin_opd, dll)
    Route::post('login', [MobileAuthController::class, 'login'])->middleware('throttle:api');

    // Auto-login TANPA LOGIN PAGE - cukup device_id
    // (admin_lapangan/admin_desa yang device-nya sudah diaktivasi)
    Route::post('auto-login', [MobileAuthController::class, 'autoLogin'])->middleware('throttle:api');

    Route::middleware('auth:sanctum')->group(function (): void {
        // Auth
        Route::get('me', [MobileAuthController::class, 'me']);
        Route::post('logout', [MobileAuthController::class, 'logout']);

        // Bootstrap for initial data load
        Route::get('bootstrap', MobileBootstrapController::class);

        // Read only family directory published by the responsible OPD.
        Route::get('master/keluarga', [MobileFieldController::class, 'families']);
        Route::get('field-points', [MobileFieldController::class, 'index']);
        Route::get('field-point-batch-statuses', [MobileFieldController::class, 'batchStatuses'])->middleware('throttle:mobile-sync');
        Route::post('field-points', [MobileFieldController::class, 'push'])->middleware('throttle:mobile-sync');

    });
});
