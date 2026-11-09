<?php

namespace Tests\Feature;

use App\Imports\DatasetValueCsvImport;
use App\Imports\DatasetValueImport;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatasetFileImportTest extends TestCase
{
    use RefreshDatabase;

    private function producer(): Dataset
    {
        $opd = Opd::create(['kode' => 'IMPORT-TEST', 'nama' => 'OPD Uji', 'aktif' => true]);
        $user = User::factory()->create(['opd_id' => $opd->id]);
        Role::findOrCreate('admin_opd', 'web');
        $user->assignRole('admin_opd');
        $this->actingAs($user);
        $dataset = Dataset::create(['opd_id' => $opd->id, 'kode' => 'IMPORT-TEST', 'nama' => 'Indikator Uji', 'period_type' => 'tahunan', 'cakupan_wilayah' => 'kabupaten', 'is_active' => true]);
        $dataset->fields()->create(['nama' => 'nilai', 'label' => 'Nilai', 'tipe' => 'decimal', 'required' => true]);
        return $dataset;
    }

    public function test_csv_bom_delimiter_zero_and_invalid_rows_are_handled(): void
    {
        $dataset = $this->producer();
        $file = tmpfile();
        try {
            fwrite($file, "\xEF\xBB\xBFtahun;periode_label;values_nilai\n2026;2026;12.5\n2026;2026;-1\n2026;2026;0\n");
            $result = (new DatasetValueCsvImport($dataset))->import(stream_get_meta_data($file)['uri']);
            $this->assertSame(2, $result['success'], implode('; ', $result['errors']));
            $this->assertSame(1, $result['failed']);
            $this->assertEquals([12.5, 0], DatasetValue::orderBy('id')->get()->map(fn ($row) => $row->values['nilai'])->all());
            $this->assertSame(2, DatasetValue::where('status', 'draft')->count());
        } finally {
            fclose($file);
        }
    }

    public function test_xlsx_uses_the_same_dataset_validation(): void
    {
        $dataset = $this->producer();
        $export = new class implements FromArray {
            public function array(): array
            {
                return [['tahun', 'periode_label', 'values_nilai'], [2026, '2026', 12.5]];
            }
        };
        $file = tmpfile();
        try {
            fwrite($file, app(Excel::class)->raw($export, Excel::XLSX));
            $import = new DatasetValueImport($dataset);
            app(Excel::class)->import($import, stream_get_meta_data($file)['uri'], null, Excel::XLSX);
            $this->assertSame(1, $import->result['success'], implode('; ', $import->result['errors']));
            $this->assertEquals(12.5, DatasetValue::firstOrFail()->values['nilai']);
            $this->assertSame('draft', DatasetValue::firstOrFail()->status);
        } finally {
            fclose($file);
        }
    }

    public function test_failed_publish_does_not_leave_a_partially_imported_row(): void
    {
        $dataset = $this->producer();
        $import = new DatasetValueImport($dataset, auth()->user(), true);
        $import->collection(collect([collect(['tahun' => 2026, 'values_nilai' => 12.5])]));
        $this->assertSame(1, $import->result['failed']);
        $this->assertDatabaseCount('dataset_values', 0);
        $this->assertDatabaseCount('workflow_logs', 0);
    }
}
