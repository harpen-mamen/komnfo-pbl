<?php

namespace App\Http\Controllers\Api;

use App\Exports\RumahExport;
use App\Exports\KeluargaExport;
use App\Exports\PendudukExport;
use App\Exports\EmptyExport;
use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Support\PanelAccess;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    private function rumahQuery(): Builder
    {
        abort_unless(
            PanelAccess::canAccessKabupatenPanel()
                || PanelAccess::canAccessKecamatanPanel()
                || PanelAccess::canAccessLapanganPanel(),
            403
        );

        abort(410, 'Laporan rumah tangga dinonaktifkan. Gunakan Katalog Dataset dan Verifikasi Data OPD.');
    }

    private function keluargaQuery(): Builder
    {
        abort_unless(PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner(), 403);

        abort(410, 'Laporan keluarga lama dinonaktifkan. Gunakan dataset agregat yang telah diterbitkan.');
    }

    private function pendudukQuery(): Builder
    {
        abort_unless(PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner(), 403);

        abort(410, 'Laporan penduduk lama dinonaktifkan. Gunakan dataset agregat yang telah diterbitkan.');
    }

    private function wrapHtml(string $title, string $body): string
    {
        return "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>$title</title>"
            . "<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}"
            . " h1{font-size:18px;margin:0 0 12px;}"
            . " table{border-collapse:collapse;width:100%;}"
            . " th,td{border:1px solid #999;padding:4px 6px;text-align:left;}"
            . " th{background:#eee;}</style></head><body><h1>$title</h1>$body</body></html>";
    }

    public function rumahPdf(Request $request): Response
    {
        $this->rumahQuery();
    }

    public function rumahExcel(Request $request): BinaryFileResponse
    {
        $this->rumahQuery();
    }

    public function keluargaPdf(Request $request): Response
    {
        $this->keluargaQuery();
    }

    public function keluargaExcel(Request $request): BinaryFileResponse
    {
        $this->keluargaQuery();
    }

    public function pendudukPdf(Request $request): Response
    {
        $this->pendudukQuery();
    }

    public function pendudukExcel(Request $request): BinaryFileResponse
    {
        $this->pendudukQuery();
    }

    public function pendidikanBulananPdf(Request $request): Response
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOpdPanel() || PanelAccess::canAccessOperatorPanel(), 403);
        $html = $this->wrapHtml('Laporan Pendidikan Bulanan', '<p>Belum ada laporan pendidikan bulanan yang bersumber dari dataset terbit.</p>');
        return Pdf::loadHTML($html)->download('laporan-pendidikan-bulanan-'.date('Y-m-d').'.pdf');
    }

    public function pendidikanBulananExcel(Request $request): BinaryFileResponse
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOpdPanel() || PanelAccess::canAccessOperatorPanel(), 403);
        // DataPendidikanBulanan table removed - return empty data
        return Excel::download(new EmptyExport(), 'laporan-pendidikan-bulanan-'.date('Y-m-d').'.xlsx');
    }

    public function kesehatanBulananPdf(Request $request): Response
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOpdPanel() || PanelAccess::canAccessOperatorPanel(), 403);
        // DataKesehatanBulanan table removed - return empty report
        $html = $this->wrapHtml('Laporan Kesehatan Bulanan', '<p>Modul kesehatan bulanan sedang dalam pengembangan.</p>');
        return Pdf::loadHTML($html)->download('laporan-kesehatan-bulanan-'.date('Y-m-d').'.pdf');
    }

    public function kesehatanBulananExcel(Request $request): BinaryFileResponse
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOpdPanel() || PanelAccess::canAccessOperatorPanel(), 403);
        // DataKesehatanBulanan table removed - return empty data
        return Excel::download(new EmptyExport(), 'laporan-kesehatan-bulanan-'.date('Y-m-d').'.xlsx');
    }
}
