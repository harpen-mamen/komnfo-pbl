<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendidikanBulananExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(fn ($d) => [
            'ID' => $d->id,
            'Kecamatan' => $d->kecamatan?->nama,
            'Periode' => $d->periode,
            'Jumlah Siswa' => $d->jumlah_siswa,
            'Jumlah Guru' => $d->jumlah_guru,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Kecamatan', 'Periode', 'Jumlah Siswa', 'Jumlah Guru'];
    }
}
