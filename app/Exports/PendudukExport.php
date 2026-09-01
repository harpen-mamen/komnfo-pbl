<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendudukExport implements FromCollection, WithHeadings
{
    protected $penduduk;

    public function __construct($penduduk)
    {
        $this->penduduk = $penduduk;
    }

    public function collection()
    {
        return $this->penduduk->map(fn ($p) => [
            'ID' => $p->id,
            'NIK' => $p->nik,
            'Nama' => $p->nama,
            'Jenis Kelamin' => $p->jenis_kelamin,
            'Tempat Lahir' => $p->tempat_lahir,
            'Tanggal Lahir' => $p->tanggal_lahir,
            'KK' => $p->keluarga?->no_kk,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'NIK', 'Nama', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'KK'];
    }
}
