<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KesehatanBulananExport implements FromCollection, WithHeadings
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
            'ISPA' => $d->ispa,
            'DBD' => $d->dbd,
            'Malaria' => $d->malaria,
            'TBC' => $d->tbc,
            'Hipertensi' => $d->hipertensi,
            'Diabetes' => $d->diabetes,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Kecamatan', 'Periode', 'ISPA', 'DBD', 'Malaria', 'TBC', 'Hipertensi', 'Diabetes'];
    }
}
