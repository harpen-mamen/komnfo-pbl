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
Route::get('/up', fn () => response()->json([
    'status' => 'ok',
    'service' => 'sipdt-api',
    'time' => now()->toIso8601String(),
]))->name('health');

// A simple root redirect so any stray browser visit gets a friendly hint.
Route::get('/', fn () => response()->json([
    'message' => 'SIPDT Kominfo Kepulauan Sangihe API',
    'docs' => '/api/v1',
    'admin' => '/admin',
]))->name('root');

// Laporan routes - named routes expected by Filament pages/actions.
Route::middleware(['auth:web'])->group(function (): void {
    Route::get('/laporan/rumah/pdf',   [LaporanController::class, 'rumahPdf'])->name('laporan.rumah.pdf');
    Route::get('/laporan/rumah/excel',  [LaporanController::class, 'rumahExcel'])->name('laporan.rumah.excel');
    Route::get('/laporan/keluarga/pdf', [LaporanController::class, 'keluargaPdf'])->name('laporan.keluarga.pdf');
    Route::get('/laporan/keluarga/excel', [LaporanController::class, 'keluargaExcel'])->name('laporan.keluarga.excel');
    Route::get('/laporan/penduduk/pdf',  [LaporanController::class, 'pendudukPdf'])->name('laporan.penduduk.pdf');
    Route::get('/laporan/penduduk/excel', [LaporanController::class, 'pendudukExcel'])->name('laporan.penduduk.excel');
    Route::get('/laporan/pendidikan-bulanan/pdf',  [LaporanController::class, 'pendidikanBulananPdf'])->name('laporan.pendidikan.pdf');
    Route::get('/laporan/pendidikan-bulanan/excel', [LaporanController::class, 'pendidikanBulananExcel'])->name('laporan.pendidikan.excel');
    Route::get('/laporan/kesehatan-bulanan/pdf',   [LaporanController::class, 'kesehatanBulananPdf'])->name('laporan.kesehatan.pdf');
    Route::get('/laporan/kesehatan-bulanan/excel',  [LaporanController::class, 'kesehatanBulananExcel'])->name('laporan.kesehatan.excel');
});
