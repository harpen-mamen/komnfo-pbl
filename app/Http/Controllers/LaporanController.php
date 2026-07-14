<?php

namespace App\Http\Controllers;

use App\Models\Keluarga;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Desa;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Support\AdminKecamatan;
use App\Support\PanelAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
    public function rumahPdf(Request $request): Response
    {
        abort_if(PanelAccess::canAccessOperatorPanel(), 403);

        $rows = $this->rumahQuery($request)->get();

        return Pdf::loadView('laporan.rumah', ['rows' => $rows])->download('laporan-rumah.pdf');
    }

    public function keluargaPdf(Request $request): Response
    {
        abort_if(PanelAccess::canAccessOperatorPanel(), 403);

        $rows = $this->keluargaQuery($request)->get();

        return Pdf::loadView('laporan.keluarga', ['rows' => $rows])->download('laporan-kk.pdf');
    }

    public function pendudukPdf(Request $request): Response
    {
        abort_if(PanelAccess::canAccessOperatorPanel(), 403);

        $rows = $this->pendudukQuery($request)->get();

        return Pdf::loadView('laporan.penduduk', ['rows' => $rows])->setPaper('a4', 'landscape')->download('laporan-penduduk.pdf');
    }

    public function rumahExcel(Request $request): Response
    {
        abort_if(PanelAccess::canAccessOperatorPanel(), 403);

        $rows = $this->rumahQuery($request)->get();

        return $this->excel('laporan-rumah.xls', ['Kode Rumah', 'Alamat', 'Latitude', 'Longitude', 'Status', 'Jumlah KK'], $rows, fn (Rumah $row) => [
            $row->kode_rumah,
            $row->alamat_lengkap,
            $row->latitude,
            $row->longitude,
            $row->status_rumah,
            $row->keluarga_count,
        ]);
    }

    public function keluargaExcel(Request $request): Response
    {
        abort_if(PanelAccess::canAccessOperatorPanel(), 403);

        $rows = $this->keluargaQuery($request)->get();

        return $this->excel('laporan-kk.xls', ['Nomor KK', 'Kepala Keluarga', 'Kode Rumah', 'Alamat', 'Jumlah Anggota', 'Status Ekonomi'], $rows, fn (Keluarga $row) => [
            $row->nomor_kk,
            $row->nama_kepala_keluarga,
            $row->rumah?->kode_rumah,
            $row->rumah?->alamat_lengkap,
            $row->jumlah_anggota,
            $row->status_ekonomi,
        ]);
    }

    public function pendudukExcel(Request $request): Response
    {
        abort_if(PanelAccess::canAccessOperatorPanel(), 403);

        $rows = $this->pendudukQuery($request)->get();

        return $this->excel('laporan-penduduk.xls', ['NIK', 'Nama', 'Jenis Kelamin', 'Nomor KK', 'Kode Rumah', 'Tanggal Lahir', 'Agama', 'Pendidikan', 'Pekerjaan'], $rows, fn (Penduduk $row) => [
            $row->nik,
            $row->nama_lengkap,
            $row->jenis_kelamin,
            $row->keluarga?->nomor_kk,
            $row->keluarga?->rumah?->kode_rumah,
            $row->tanggal_lahir?->format('Y-m-d'),
            $row->agama,
            $row->pendidikan,
            $row->pekerjaan,
        ]);
    }

    public function pendidikanBulananPdf(Request $request): Response
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::isOperatorSekolah(), 403);

        $rows = $this->pendidikanBulananQuery($request)->get();

        return Pdf::loadView('laporan.pendidikan-bulanan', ['rows' => $rows])->setPaper('a4', 'landscape')->download('laporan-pendidikan-bulanan.pdf');
    }

    public function pendidikanBulananExcel(Request $request): Response
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::isOperatorSekolah(), 403);

        $rows = $this->pendidikanBulananQuery($request)->get();

        return $this->excel('laporan-pendidikan-bulanan.xls', ['Periode', 'Bulan', 'Tahun', 'Sekolah', 'Kecamatan', 'Desa', 'Siswa', 'Laki-laki', 'Perempuan', 'Putus Sekolah', 'Lulus', 'Naik Kelas', 'Tidak Naik', 'Status'], $rows, fn (DataPendidikanBulanan $row) => [
            $row->periodePelaporan?->nama_periode,
            $row->periode_bulan,
            $row->periode_tahun,
            $row->sekolah,
            $row->kecamatan?->nama,
            $row->desa?->nama,
            $row->jumlah_siswa,
            $row->laki_laki,
            $row->perempuan,
            $row->putus_sekolah,
            $row->lulus,
            $row->naik_kelas,
            $row->tidak_naik,
            $row->status,
        ]);
    }

    public function kesehatanBulananPdf(Request $request): Response
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::isOperatorPuskesmas(), 403);

        $rows = $this->kesehatanBulananQuery($request)->get();

        return Pdf::loadView('laporan.kesehatan-bulanan', ['rows' => $rows])->setPaper('a4', 'landscape')->download('laporan-kesehatan-bulanan.pdf');
    }

    public function kesehatanBulananExcel(Request $request): Response
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::isOperatorPuskesmas(), 403);

        $rows = $this->kesehatanBulananQuery($request)->get();

        return $this->excel('laporan-kesehatan-bulanan.xls', ['Periode', 'Bulan', 'Tahun', 'Puskesmas', 'Kecamatan', 'Desa', 'Balita', 'Ibu Hamil', 'Stunting', 'Gizi Buruk', 'Disabilitas', 'Kematian', 'Kelahiran', 'BPJS Aktif', 'BPJS Tidak Aktif', 'Status'], $rows, fn (DataKesehatanBulanan $row) => [
            $row->periodePelaporan?->nama_periode,
            $row->periode_bulan,
            $row->periode_tahun,
            $row->puskesmas,
            $row->kecamatan?->nama,
            $row->desa?->nama,
            $row->jumlah_balita,
            $row->jumlah_ibu_hamil,
            $row->stunting,
            $row->gizi_buruk,
            $row->disabilitas,
            $row->kematian,
            $row->kelahiran,
            $row->bpjs_aktif,
            $row->bpjs_tidak_aktif,
            $row->status,
        ]);
    }

    private function excel(string $filename, array $headers, Collection $rows, callable $mapper): Response
    {
        $content = view('laporan.excel', compact('headers', 'rows', 'mapper'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function rumahQuery(Request $request): Builder
    {
        return Rumah::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->withCount('keluarga')
            ->when($this->allowedDesaId($request), fn (Builder $query, int $desaId) => $query->where('desa_id', $desaId))
            ->when($request->query('status_rumah'), fn (Builder $query, string $status) => $query->where('status_rumah', $status))
            ->when($request->query('tanggal_dari'), fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('tanggal_sampai'), fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy('kode_rumah');
    }

    private function keluargaQuery(Request $request): Builder
    {
        return Keluarga::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->with('rumah')
            ->when($this->allowedDesaId($request), fn (Builder $query, int $desaId) => $query->whereHas('rumah', fn (Builder $rumah) => $rumah->where('desa_id', $desaId)))
            ->when($request->query('status_rumah'), fn (Builder $query, string $status) => $query->whereHas('rumah', fn (Builder $rumah) => $rumah->where('status_rumah', $status)))
            ->when($request->query('status_ekonomi'), fn (Builder $query, string $status) => $query->where('status_ekonomi', $status))
            ->when($request->query('tanggal_dari'), fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('tanggal_sampai'), fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy('nomor_kk');
    }

    private function pendudukQuery(Request $request): Builder
    {
        return Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->with('keluarga.rumah')
            ->when($this->allowedDesaId($request), fn (Builder $query, int $desaId) => $query->whereHas('keluarga.rumah', fn (Builder $rumah) => $rumah->where('desa_id', $desaId)))
            ->when($request->query('status_rumah'), fn (Builder $query, string $status) => $query->whereHas('keluarga.rumah', fn (Builder $rumah) => $rumah->where('status_rumah', $status)))
            ->when($request->query('status_ekonomi'), fn (Builder $query, string $status) => $query->whereHas('keluarga', fn (Builder $keluarga) => $keluarga->where('status_ekonomi', $status)))
            ->when($request->query('tanggal_dari'), fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('tanggal_sampai'), fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy('nama_lengkap');
    }

    private function pendidikanBulananQuery(Request $request): Builder
    {
        return DataPendidikanBulanan::query()
            ->visibleToCurrentUser()
            ->with(['periodePelaporan', 'kecamatan', 'desa'])
            ->when($request->integer('periode_pelaporan_id'), fn (Builder $query, int $periodeId) => $query->where('periode_pelaporan_id', $periodeId))
            ->when($request->integer('periode_bulan'), fn (Builder $query, int $bulan) => $query->where('periode_bulan', $bulan))
            ->when($request->integer('periode_tahun'), fn (Builder $query, int $tahun) => $query->where('periode_tahun', $tahun))
            ->when($this->allowedKecamatanId($request), fn (Builder $query, int $kecamatanId) => $query->where('kecamatan_id', $kecamatanId))
            ->when($this->allowedDesaId($request), fn (Builder $query, int $desaId) => $query->where('desa_id', $desaId))
            ->when($request->query('sekolah'), fn (Builder $query, string $sekolah) => $query->where('sekolah', 'like', "%{$sekolah}%"))
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->orderBy('sekolah');
    }

    private function kesehatanBulananQuery(Request $request): Builder
    {
        return DataKesehatanBulanan::query()
            ->visibleToCurrentUser()
            ->with(['periodePelaporan', 'kecamatan', 'desa'])
            ->when($request->integer('periode_pelaporan_id'), fn (Builder $query, int $periodeId) => $query->where('periode_pelaporan_id', $periodeId))
            ->when($request->integer('periode_bulan'), fn (Builder $query, int $bulan) => $query->where('periode_bulan', $bulan))
            ->when($request->integer('periode_tahun'), fn (Builder $query, int $tahun) => $query->where('periode_tahun', $tahun))
            ->when($this->allowedKecamatanId($request), fn (Builder $query, int $kecamatanId) => $query->where('kecamatan_id', $kecamatanId))
            ->when($this->allowedDesaId($request), fn (Builder $query, int $desaId) => $query->where('desa_id', $desaId))
            ->when($request->query('puskesmas'), fn (Builder $query, string $puskesmas) => $query->where('puskesmas', 'like', "%{$puskesmas}%"))
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->orderBy('puskesmas');
    }

    private function allowedKecamatanId(Request $request): ?int
    {
        $kecamatanId = $request->integer('kecamatan_id') ?: null;

        if (! $kecamatanId || PanelAccess::canAccessKabupatenPanel()) {
            return $kecamatanId;
        }

        return (int) PanelAccess::user()?->kecamatan_id === $kecamatanId ? $kecamatanId : null;
    }

    private function allowedDesaId(Request $request): ?int
    {
        $desaId = $request->integer('desa_id') ?: null;

        if (! $desaId) {
            return null;
        }

        if (PanelAccess::isAdminKecamatan()) {
            return AdminKecamatan::canUseDesa($desaId) ? $desaId : null;
        }

        if (PanelAccess::canAccessOperatorPanel()) {
            return Desa::query()->whereKey($desaId)->where('kecamatan_id', PanelAccess::user()?->kecamatan_id)->exists() ? $desaId : null;
        }

        return $desaId;
    }
}
