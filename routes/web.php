<?php

use App\Filament\Pages\KirimDataKeKominfo;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// (debug route removed)

Route::middleware(['auth'])->group(function () {
    Route::post('/admin/kirim-data-kominfo/submit', [KirimDataKeKominfo::class, 'submit'])
        ->name('filament.pages.kirim-data-kominfo.submit');

    // API endpoint to trigger snapshot email manually (for testing / webhook)
    Route::post('/admin/submission-batches/{batch}/notify-kominfo', function (\App\Models\SubmissionBatch $batch) {
        $recipients = \App\Models\User::query()->role('admin')->get();

        if ($recipients->isEmpty()) {
            return response()->json(['message' => 'No recipients'], 204);
        }

        if (! $batch->payload_snapshot) {
            return response()->json(['message' => 'No snapshot available'], 422);
        }

        \Illuminate\Support\Facades\Mail::to($recipients->pluck('email')->toArray())
            ->send(new \App\Mail\SubmissionSnapshotMail($batch->payload_snapshot, $batch->kecamatan?->nama ?? ''));

        return response()->json(['message' => 'Notification sent']);
    })->middleware('auth');

    Route::get('/laporan/rumah/pdf', [LaporanController::class, 'rumahPdf'])->name('laporan.rumah.pdf');
    Route::get('/laporan/rumah/excel', [LaporanController::class, 'rumahExcel'])->name('laporan.rumah.excel');
    Route::get('/laporan/keluarga/pdf', [LaporanController::class, 'keluargaPdf'])->name('laporan.keluarga.pdf');
    Route::get('/laporan/keluarga/excel', [LaporanController::class, 'keluargaExcel'])->name('laporan.keluarga.excel');
    Route::get('/laporan/penduduk/pdf', [LaporanController::class, 'pendudukPdf'])->name('laporan.penduduk.pdf');
    Route::get('/laporan/penduduk/excel', [LaporanController::class, 'pendudukExcel'])->name('laporan.penduduk.excel');
    Route::get('/laporan/pendidikan-bulanan/pdf', [LaporanController::class, 'pendidikanBulananPdf'])->name('laporan.pendidikan-bulanan.pdf');
    Route::get('/laporan/pendidikan-bulanan/excel', [LaporanController::class, 'pendidikanBulananExcel'])->name('laporan.pendidikan-bulanan.excel');
    Route::get('/laporan/kesehatan-bulanan/pdf', [LaporanController::class, 'kesehatanBulananPdf'])->name('laporan.kesehatan-bulanan.pdf');
    Route::get('/laporan/kesehatan-bulanan/excel', [LaporanController::class, 'kesehatanBulananExcel'])->name('laporan.kesehatan-bulanan.excel');
});
