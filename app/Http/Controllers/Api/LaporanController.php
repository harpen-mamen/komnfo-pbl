<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
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
        $rumah = Rumah::with(['desa.kecamatan', 'keluarga'])->get();
        $rows = '';
        foreach ($rumah as $r) {
            $rows .= '<tr><td>'.$r->id.'</td><td>'.($r->desa?->kecamatan?->nama ?? '-').'</td><td>'
                .($r->desa?->nama ?? '-').'</td><td>'.($r->alamat ?? '-').'</td><td>'
                .($r->keluarga->first()?->nama_kepala_keluarga ?? '-').'</td></tr>';
        }
        $html = $this->wrapHtml('Laporan Rumah', '<table><thead><tr><th>ID</th><th>Kecamatan</th><th>Desa</th><th>Alamat</th><th>Kepala Keluarga</th></tr></thead><tbody>'.$rows.'</tbody></table>');
        return Pdf::loadHTML($html)->download('laporan-rumah-'.date('Y-m-d').'.pdf');
    }

    public function rumahExcel(Request $request): BinaryFileResponse
    {
        $rumah = Rumah::with(['desa.kecamatan', 'keluarga'])->get();
        return Excel::download(new \App\Exports\RumahExport($rumah), 'laporan-rumah-'.date('Y-m-d').'.xlsx');
    }

    public function keluargaPdf(Request $request): Response
    {
        $keluarga = Keluarga::with(['rumah.desa.kecamatan', 'penduduk'])->get();
        $rows = '';
        foreach ($keluarga as $k) {
            $rows .= '<tr><td>'.$k->id.'</td><td>'.($k->no_kk ?? '-').'</td><td>'
                .($k->nama_kepala_keluarga ?? '-').'</td><td>'.($k->rumah?->alamat ?? '-').'</td><td>'
                .($k->rumah?->desa?->nama ?? '-').'</td><td>'.$k->penduduk->count().'</td></tr>';
        }
        $html = $this->wrapHtml('Laporan Keluarga', '<table><thead><tr><th>ID</th><th>No KK</th><th>Kepala Keluarga</th><th>Alamat</th><th>Desa</th><th>Jumlah Anggota</th></tr></thead><tbody>'.$rows.'</tbody></table>');
        return Pdf::loadHTML($html)->download('laporan-keluarga-'.date('Y-m-d').'.pdf');
    }

    public function keluargaExcel(Request $request): BinaryFileResponse
    {
        $keluarga = Keluarga::with(['rumah.desa.kecamatan', 'penduduk'])->get();
        return Excel::download(new \App\Exports\KeluargaExport($keluarga), 'laporan-keluarga-'.date('Y-m-d').'.xlsx');
    }

    public function pendudukPdf(Request $request): Response
    {
        $penduduk = Penduduk::with(['keluarga.rumah.desa.kecamatan'])->get();
        $rows = '';
        foreach ($penduduk as $p) {
            $rows .= '<tr><td>'.$p->id.'</td><td>'.($p->nik ?? '-').'</td><td>'
                .($p->nama ?? '-').'</td><td>'.($p->jenis_kelamin ?? '-').'</td><td>'
                .($p->tempat_lahir ?? '-').'</td><td>'.($p->tanggal_lahir ?? '-').'</td></tr>';
        }
        $html = $this->wrapHtml('Laporan Penduduk', '<table><thead><tr><th>ID</th><th>NIK</th><th>Nama</th><th>JK</th><th>Tempat Lahir</th><th>Tanggal Lahir</th></tr></thead><tbody>'.$rows.'</tbody></table>');
        return Pdf::loadHTML($html)->download('laporan-penduduk-'.date('Y-m-d').'.pdf');
    }

    public function pendudukExcel(Request $request): BinaryFileResponse
    {
        $penduduk = Penduduk::with(['keluarga.rumah.desa.kecamatan'])->get();
        return Excel::download(new \App\Exports\PendudukExport($penduduk), 'laporan-penduduk-'.date('Y-m-d').'.xlsx');
    }

    public function pendidikanBulananPdf(Request $request): Response
    {
        $data = DataPendidikanBulanan::with('kecamatan')->get();
        $rows = '';
        foreach ($data as $d) {
            $rows .= '<tr><td>'.$d->id.'</td><td>'.($d->kecamatan?->nama ?? '-').'</td><td>'
                .($d->periode ?? '-').'</td><td>'.($d->jumlah_siswa ?? 0).'</td><td>'.($d->jumlah_guru ?? 0).'</td></tr>';
        }
        $html = $this->wrapHtml('Laporan Pendidikan Bulanan', '<table><thead><tr><th>ID</th><th>Kecamatan</th><th>Periode</th><th>Siswa</th><th>Guru</th></tr></thead><tbody>'.$rows.'</tbody></table>');
        return Pdf::loadHTML($html)->download('laporan-pendidikan-bulanan-'.date('Y-m-d').'.pdf');
    }

    public function pendidikanBulananExcel(Request $request): BinaryFileResponse
    {
        $data = DataPendidikanBulanan::with('kecamatan')->get();
        return Excel::download(new \App\Exports\PendidikanBulananExport($data), 'laporan-pendidikan-bulanan-'.date('Y-m-d').'.xlsx');
    }

    public function kesehatanBulananPdf(Request $request): Response
    {
        $data = DataKesehatanBulanan::with('kecamatan')->get();
        $rows = '';
        foreach ($data as $d) {
            $rows .= '<tr><td>'.$d->id.'</td><td>'.($d->kecamatan?->nama ?? '-').'</td><td>'
                .($d->periode ?? '-').'</td><td>'.($d->ispa ?? 0).'</td><td>'.($d->dbd ?? 0).'</td><td>'
                .($d->malaria ?? 0).'</td><td>'.($d->tbc ?? 0).'</td><td>'.($d->hipertensi ?? 0).'</td><td>'.($d->diabetes ?? 0).'</td></tr>';
        }
        $html = $this->wrapHtml('Laporan Kesehatan Bulanan', '<table><thead><tr><th>ID</th><th>Kecamatan</th><th>Periode</th><th>ISPA</th><th>DBD</th><th>Malaria</th><th>TBC</th><th>Hipertensi</th><th>Diabetes</th></tr></thead><tbody>'.$rows.'</tbody></table>');
        return Pdf::loadHTML($html)->download('laporan-kesehatan-bulanan-'.date('Y-m-d').'.pdf');
    }

    public function kesehatanBulananExcel(Request $request): BinaryFileResponse
    {
        $data = DataKesehatanBulanan::with('kecamatan')->get();
        return Excel::download(new \App\Exports\KesehatanBulananExport($data), 'laporan-kesehatan-bulanan-'.date('Y-m-d').'.xlsx');
    }
}
