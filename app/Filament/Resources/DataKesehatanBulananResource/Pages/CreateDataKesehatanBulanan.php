<?php

namespace App\Filament\Resources\DataKesehatanBulananResource\Pages;

use App\Filament\Resources\DataKesehatanBulananResource;
use App\Models\Desa;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use App\Models\KasusPenyakit;
use Filament\Resources\Pages\CreateRecord;

class CreateDataKesehatanBulanan extends CreateRecord
{
    protected static string $resource = DataKesehatanBulananResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $periode = PeriodePelaporan::query()->findOrFail($data['periode_pelaporan_id']);

        if (PanelAccess::isAdminKecamatan()) {
            abort_unless($periode->isOpenForInput(), 403);
        }

        $data['periode_bulan'] = $periode->bulan;
        $data['periode_tahun'] = $periode->tahun;

        if (PanelAccess::isAdminKecamatan()) {
            $data['kecamatan_id'] = PanelAccess::user()?->kecamatan_id;
            $data['status'] = 'Terkirim';
        }

        $data['desa_id'] = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ((array) ($this->data['kasus_penyakit_detail'] ?? []) as $case) {
            if (empty($case['penduduk_id']) || empty($case['jenis_penyakit'])) continue;
            $penduduk = \App\Models\Penduduk::query()->with('keluarga.rumah')->find($case['penduduk_id']);
            if (! $penduduk) continue;
            KasusPenyakit::create(['penduduk_id'=>$penduduk->id,'desa_id'=>$penduduk->keluarga?->rumah?->desa_id,'kecamatan_id'=>PanelAccess::user()?->kecamatan_id,'data_kesehatan_bulanan_id'=>$this->record->id,'jenis_penyakit'=>$case['jenis_penyakit'],'tanggal_diagnosis'=>$case['tanggal_diagnosis'] ?? now(),'status_kasus'=>$case['status_kasus'] ?? 'aktif','catatan'=>$case['catatan'] ?? null,'created_by'=>auth()->id()]);
        }
    }
}
