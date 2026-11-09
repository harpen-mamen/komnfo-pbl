<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class EmptyExport implements FromCollection
{
    public function collection()
    {
        return new Collection([['Data tidak tersedia - Modul dalam pengembangan']]);
    }
}
