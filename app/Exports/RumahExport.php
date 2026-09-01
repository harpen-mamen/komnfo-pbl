<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RumahExport implements FromCollection, WithHeadings
{
    protected $rumah;

    public function __construct($rumah)
    {
        $this->rumah = $rumah;
    }

    public function collection()
    {
        return $this->rumah->map(fn ($r) => [
            'ID' => $r->id,
            'Kecamatan' => $r->desa?->kecamatan?->nama,
            'Desa' => $r->desa?->nama,
            'Alamat' => $r->alamat,
            'Kepala Keluarga' => $r->keluarga->first()?->nama_kepala_keluarga,
            'Latitude' => $r->latitude,
            'Longitude' => $r->longitude,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Kecamatan', 'Desa', 'Alamat', 'Kepala Keluarga', 'Latitude', 'Longitude'];
    }
}
