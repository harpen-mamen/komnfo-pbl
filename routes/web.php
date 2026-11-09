<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LaporanController;

/*
|--------------------------------------------------------------------------
| Web Routes (API-Only Mode)
|--------------------------------------------------------------------------
|
| This application is now an API-only backend serving:
|   - Next.js frontend (public web portal)        -> /api/v1/public/*
|   - Flutter mobile app (Citizen data collector)  -> /api/v1/mobile/*
|   - Filament admin panel (Kominfo dashboard)     -> /admin/*
|
| Filament automatically registers the /admin/* routes via its service
| provider. This file is intentionally minimal so Laravel's router does
| not throw "web routes are empty" warnings.
|
*/

// Health-check used by uptime monitors and the Next.js frontend.
// Preserve bookmarks for retired duplicate pages.
Route::middleware(['auth:web', \App\Http\Middleware\BupatiDashboardOnly::class])->prefix(config('filament.path', 'admin'))->group(function (): void {
    Route::get('input-data-opd', fn () => new \Illuminate\Http\RedirectResponse(\App\Filament\Pages\DataOpd::getUrl()))->name('filament.admin.pages.input-data-opd');
    Route::get('opd-dashboard', fn () => new \Illuminate\Http\RedirectResponse(url(config('filament.path', 'admin'))))->name('filament.admin.pages.opd-dashboard');
    Route::get('monitoring-kabupaten', function () {
        abort_unless(\App\Support\PanelAccess::canAccessKabupatenPanel(), 403);
        return new \Illuminate\Http\RedirectResponse(\App\Filament\Pages\ExecutiveDashboard::getUrl());
    })->name('filament.admin.pages.monitoring-kabupaten');
    Route::get('import-data-penduduk', fn () => new \Illuminate\Http\RedirectResponse(\App\Filament\Pages\DataOpd::getUrl()))->name('filament.admin.pages.import-data-penduduk');
    Route::get('penduduks', fn () => new \Illuminate\Http\RedirectResponse(\App\Filament\Pages\DataOpd::getUrl()))->name('filament.admin.resources.penduduks.index');
    Route::get('keluargas', fn () => new \Illuminate\Http\RedirectResponse(\App\Filament\Pages\DataOpd::getUrl()))->name('filament.admin.resources.keluargas.index');
    Route::get('laporan', fn () => new \Illuminate\Http\RedirectResponse(\App\Filament\Pages\DataOpd::getUrl()))->name('filament.admin.pages.laporan');
});

Route::get('/up', fn () => response()->json([
    'status' => 'ok',
    'service' => 'sipdt-api',
    'time' => now()->toIso8601String(),
]))->name('health');

// Public landing page - SIPANDU (clean, bold, elegant)
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Laporan routes - named routes expected by Filament pages/actions.
Route::middleware(['auth:web', \App\Http\Middleware\BupatiDashboardOnly::class])->group(function (): void {
    Route::post('/admin/verifikasi-data-sektoral/{submissionId}/verify', [App\Http\Controllers\DatasetSubmissionReviewController::class, 'verify'])
        ->name('dataset-submissions.verify');
    Route::post('/admin/verifikasi-data-sektoral/{submissionId}/revision', [App\Http\Controllers\DatasetSubmissionReviewController::class, 'requestRevision'])
        ->name('dataset-submissions.revision');

    Route::get('/laporan/rumah/pdf', [LaporanController::class, 'rumahPdf'])->name('laporan.rumah.pdf');
    Route::get('/laporan/rumah/excel', [LaporanController::class, 'rumahExcel'])->name('laporan.rumah.excel');
    Route::get('/laporan/keluarga/pdf', [LaporanController::class, 'keluargaPdf'])->middleware('can:export-identitas')->name('laporan.keluarga.pdf');
    Route::get('/laporan/keluarga/excel', [LaporanController::class, 'keluargaExcel'])->middleware('can:export-identitas')->name('laporan.keluarga.excel');
    Route::get('/laporan/penduduk/pdf', [LaporanController::class, 'pendudukPdf'])->middleware('can:export-identitas')->name('laporan.penduduk.pdf');
    Route::get('/laporan/penduduk/excel', [LaporanController::class, 'pendudukExcel'])->middleware('can:export-identitas')->name('laporan.penduduk.excel');
    Route::get('/laporan/pendidikan-bulanan/pdf', [LaporanController::class, 'pendidikanBulananPdf'])->name('laporan.pendidikan.pdf');
    Route::get('/laporan/pendidikan-bulanan/excel', [LaporanController::class, 'pendidikanBulananExcel'])->name('laporan.pendidikan.excel');
    Route::get('/laporan/kesehatan-bulanan/pdf', [LaporanController::class, 'kesehatanBulananPdf'])->name('laporan.kesehatan.pdf');
    Route::get('/laporan/kesehatan-bulanan/excel', [LaporanController::class, 'kesehatanBulananExcel'])->name('laporan.kesehatan.excel');
});

// Dataset Export Routes
Route::middleware(['auth:web', \App\Http\Middleware\BupatiDashboardOnly::class])->group(function (): void {
    Route::post('/admin/kirim-data-sektoral', [App\Http\Controllers\DatasetSubmissionController::class, 'store'])
        ->name('dataset-submissions.store');

    Route::get('/admin/dataset/{kode}/export', [App\Http\Controllers\Api\DatasetExportController::class, 'export'])
        ->name('dataset.export');
    Route::get('/admin/dataset/{kode}/stats', [App\Http\Controllers\Api\DatasetExportController::class, 'stats'])
        ->name('dataset.stats');
});

// Dataset Import Routes
Route::middleware(['auth:web', \App\Http\Middleware\BupatiDashboardOnly::class])->group(function (): void {
    Route::get('/admin/dataset/{kode}/import-template', [App\Http\Controllers\Api\DatasetImportController::class, 'template'])
        ->name('dataset.import.template');
    Route::post('/admin/dataset/{kode}/import', [App\Http\Controllers\Api\DatasetImportController::class, 'import'])
        ->name('dataset.import');
});
