<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** XLS/XLSX adapter sharing validation and transactions with the CSV importer. */
class DatasetValueImport extends DatasetValueCsvImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->result['errors'][] = 'Berkas tidak memiliki baris data.';
            return;
        }

        foreach ($rows as $index => $row) {
            $this->importRow($row->all(), $index + 2);
        }
    }
}
