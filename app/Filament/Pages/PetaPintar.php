<?php

namespace App\Filament\Pages;

use App\Models\Desa;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\DataKesehatanBulanan;
use App\Support\PanelAccess;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Request;

class PetaPintar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Peta Pintar';
    protected static ?string $title = 'Peta Pintar';
    protected static ?int $navigationSort = 9;
    protected static string $view = 'filament.pages.peta-pintar';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan() || 
               PanelAccess::isKominfoKabupaten() || 
               false || 
               PanelAccess::isOperatorOpd();
    }

    public function mount(): void
    {
        abort_unless(
            PanelAccess::isAdminKecamatan() || 
            PanelAccess::isKominfoKabupaten() || 
            false || 
            PanelAccess::isOperatorOpd(), 
            403
        );
    }

    public function getTitle(): string
    {
        if (PanelAccess::isKominfoKabupaten()) {
            return 'Peta Pintar Kabupaten';
        } elseif (PanelAccess::isAdminDesa()) {
            return 'Peta Pintar Desa';
        } elseif (PanelAccess::isOperatorOpd()) {
            return 'Peta Pintar OPD';
        }
        return 'Peta Pintar Kecamatan';
    }

    public function getMarkersProperty(): array
    {
        return Rumah::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->with([
                'desa',
                'keluarga' => fn ($query) => $query->withCount('penduduk')->with(['penduduk' => fn ($q) => $q->select('id', 'keluarga_id', 'nama_lengkap', 'jenis_kelamin', 'tanggal_lahir')])
            ])
            ->withCount(['keluarga'])
            ->get()
            ->map(fn (Rumah $rumah) => [
                'id' => $rumah->id,
                'kode_rumah' => $rumah->kode_rumah,
                'alamat' => $rumah->alamat_lengkap,
                'latitude' => (float) $rumah->latitude,
                'longitude' => (float) $rumah->longitude,
                'jumlah_kk' => $rumah->keluarga_count,
                'jumlah_penduduk' => $rumah->keluarga->sum('penduduk_count'),
                'status_rumah' => $rumah->status_rumah,
                'desa' => $rumah->desa?->nama ?? 'Tidak Diketahui',
                'density' => $rumah->keluarga_count > 5 ? 'high' : ($rumah->keluarga_count > 2 ? 'medium' : 'low'),
                'keluarga' => $rumah->keluarga->map(fn ($k) => [
                    'id' => $k->id,
                    'nama_kepala_keluarga' => $k->nama_kepala_keluarga,
                    'nomor_kk' => $k->nomor_kk,
                    'status_ekonomi' => $k->status_ekonomi,
                    'penduduk' => $k->penduduk->map(fn ($p) => [
                        'id' => $p->id,
                        'nama_lengkap' => $p->nama_lengkap,
                        'jenis_kelamin' => $p->jenis_kelamin,
                        'tanggal_lahir' => $p->tanggal_lahir?->format('d-m-Y'),
                    ])->toArray(),
                ])->toArray(),
            ])
            ->values()
            ->all();
    }

    public function getDesasProperty(): array
    {
        $query = Desa::query();
        
        // Filter based on user role
        if (PanelAccess::isAdminKecamatan() || PanelAccess::isAdminDesa()) {
            $query->where('kecamatan_id', auth()->user()?->kecamatan_id);
        } elseif (PanelAccess::isOperatorOpd()) {
            // OPD sees all desa
            // No additional filter
        }
        // Kominfo Kabupaten sees all
        
        return $query->pluck('nama', 'id')->toArray();
    }

    public function getDiseaseDistributionProperty(): array
    {
        $fields = ['ispa', 'dbd', 'malaria', 'tbc', 'hipertensi', 'diabetes', 'covid', 'lainnya', 'stunting', 'gizi_buruk'];
        $query = DataKesehatanBulanan::query()->inApprovedBatch();
        if (! PanelAccess::canAccessKabupatenPanel()) {
            $query->where('kecamatan_id', auth()->user()?->kecamatan_id);
        }
        return $query->with(['kecamatan', 'desa'])->select(array_merge(['kecamatan_id', 'desa_id'], $fields))->get()
            ->groupBy(fn ($row) => $row->desa_id ? 'desa:'.mb_strtolower($row->desa?->nama ?? '') : 'kecamatan:'.mb_strtolower($row->kecamatan?->nama ?? ''))
            ->map(fn ($rows) => collect($fields)->mapWithKeys(fn ($field) => [$field => (int) $rows->sum($field)])->all())
            ->all();
    }

    /**
     * Get search suggestions based on family head name and linked house location
     */
    public function getPendudukSearchProperty(): array
    {
        return Keluarga::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->with(['rumah.desa', 'penduduk'])
            ->select('id', 'rumah_id', 'nomor_kk', 'nama_kepala_keluarga', 'status_ekonomi')
            ->orderBy('nama_kepala_keluarga')
            ->limit(1000)
            ->get()
            ->map(fn (Keluarga $keluarga) => [
                'id' => $keluarga->id,
                'nama' => $keluarga->nama_kepala_keluarga,
                'nama_kepala_keluarga' => $keluarga->nama_kepala_keluarga,
                'nomor_kk' => $keluarga->nomor_kk,
                'status_ekonomi' => $keluarga->status_ekonomi,
                'rumah_id' => $keluarga->rumah_id,
                'kode_rumah' => $keluarga->rumah?->kode_rumah,
                'alamat' => $keluarga->rumah?->alamat_lengkap,
                'desa' => $keluarga->rumah?->desa?->nama,
                'lat' => (float) ($keluarga->rumah?->latitude ?? 0),
                'lng' => (float) ($keluarga->rumah?->longitude ?? 0),
                'penduduk_count' => $keluarga->penduduk->count() ?? 0,
                'anggota' => $keluarga->penduduk->map(fn ($penduduk) => [
                    'nama' => $penduduk->nama_lengkap,
                    'status' => $penduduk->status_dalam_keluarga,
                ])->toArray(),
            ])
            ->toArray();
    }

    /**
     * Get detailed resident information
     */
    public function getResidentDetailProperty(): array
    {
        $residenId = Request::input('residen_id');
        
        if (!$residenId) {
            return [];
        }

        $penduduk = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->with(['keluarga' => fn ($q) => $q->with(['rumah.desa', 'penduduk'])])
            ->find($residenId);

        if (!$penduduk) {
            return [];
        }

        return [
            'id' => $penduduk->id,
            'nama_lengkap' => $penduduk->nama_lengkap,
            'nik' => $penduduk->nik,
            'jenis_kelamin' => $penduduk->jenis_kelamin,
            'tanggal_lahir' => $penduduk->tanggal_lahir?->format('d-m-Y'),
            'agama' => $penduduk->agama,
            'pendidikan' => $penduduk->pendidikan,
            'pekerjaan' => $penduduk->pekerjaan,
            'penghasilan' => $penduduk->penghasilan,
            'status_perkawinan' => $penduduk->status_perkawinan,
            'status_dalam_keluarga' => $penduduk->status_dalam_keluarga,
            'nomor_hp' => $penduduk->nomor_hp,
            'kode_rumah' => $penduduk->keluarga?->rumah?->kode_rumah,
            'alamat_lengkap' => $penduduk->keluarga?->rumah?->alamat_lengkap,
            'desa' => $penduduk->keluarga?->rumah?->desa?->nama,
            'nomor_kk' => $penduduk->keluarga?->nomor_kk,
            'nama_kepala_keluarga' => $penduduk->keluarga?->nama_kepala_keluarga,
            'status_ekonomi' => $penduduk->keluarga?->status_ekonomi,
            'latitude' => (float) ($penduduk->keluarga?->rumah?->latitude ?? 0),
            'longitude' => (float) ($penduduk->keluarga?->rumah?->longitude ?? 0),
            'anggota_keluarga' => $penduduk->keluarga?->penduduk->map(fn ($p) => [
                'nama' => $p->nama_lengkap,
                'jenis_kelamin' => $p->jenis_kelamin,
                'status' => $p->status_dalam_keluarga,
                'tanggal_lahir' => $p->tanggal_lahir?->format('d-m-Y'),
            ])->toArray() ?? [],
        ];
    }
}
