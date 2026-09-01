<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KeluargaExport implements FromCollection, WithHeadings
{
    protected $keluarga;

    public function __construct($keluarga)
    {
        $this->keluarga = $keluarga;
    }

    public function collection()
    {
        return $this->keluarga->map(fn ($k) => [
            'ID' => $k->id,
            'No KK' => $k->no_kk,
            'Kepala Keluarga' => $k->nama_kepala_keluarga,
            'Alamat' => $k->rumah?->alamat,
            'Desa' => $k->rumah?->desa?->nama,
            'Jumlah Anggota' => $k->penduduk->count(),
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'No KK', 'Kepala Keluarga', 'Alamat', 'Desa', 'Jumlah Anggota'];
    }
}
