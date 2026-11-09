<?php

namespace App\Imports;

use Throwable;
use App\Models\Dataset;
use App\Models\User;
use App\Services\DataInput\DatasetValueManager;
use App\Services\DataInput\FieldAutoCalculator;
use Illuminate\Support\Facades\DB;

/**
 * CSV parser and shared, transactional row processing for CSV/XLS/XLSX imports.
 *
 * Format CSV:
 * Header: tahun,periode_label,kecamatan_id,values_*,values_*
 * Rows: data per baris
 */
class DatasetValueCsvImport
{
    public array $result = ['success' => 0, 'failed' => 0, 'errors' => []];

    public function __construct(
        public Dataset $dataset,
        public ?User $user = null,
        public bool $autoPublish = false,
    ) {
        $this->user = $user ?? auth()->user();
    }

    /**
     * Import from file path (CSV)
     */
    public function import(string $filePath): array
    {
        $this->result = ['success' => 0, 'failed' => 0, 'errors' => []];
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            $this->result['errors'][] = 'Tidak dapat membuka file';
            return $this->result;
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                $this->result['errors'][] = 'File kosong atau tidak valid';
                return $this->result;
            }
            rewind($handle);
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            $headers = fgetcsv($handle, 0, $delimiter, '"', '');
            if (! $headers) {
                $this->result['errors'][] = 'Header CSV tidak ditemukan';
                return $this->result;
            }
            $headers = array_map(fn ($header) => strtolower(ltrim(trim((string) $header), "\xEF\xBB\xBF")), $headers);
            if (count(array_unique($headers)) !== count($headers) || in_array('', $headers, true)) {
                $this->result['errors'][] = 'Header CSV kosong atau berulang. Gunakan template dataset.';
                return $this->result;
            }
            $rowNumber = 1;
            while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
                $rowNumber++;
                if (count($row) > count($headers)) {
                    $this->result['errors'][] = "Baris {$rowNumber}: jumlah kolom melebihi header.";
                    $this->result['failed']++;
                    continue;
                }
                $this->importRow(array_combine($headers, array_pad($row, count($headers), null)), $rowNumber);
            }
        } finally {
            fclose($handle);
        }

        return $this->result;
    }

    protected function importRow(array $row, int $rowNumber): void
    {
        if (array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '') === []) {
            return;
        }

        try {
            $this->dataset->loadMissing('fields');
            $fieldNames = $this->dataset->fields->pluck('nama')->all();
            $values = [];
            foreach ($row as $key => $value) {
                if (! str_starts_with((string) $key, 'values_') || $value === null || $value === '') {
                    continue;
                }
                $name = substr($key, 7);
                if (! in_array($name, $fieldNames, true)) {
                    throw new \InvalidArgumentException("Kolom {$key} tidak termasuk dalam dataset. Gunakan template yang sesuai.");
                }
                $values[$name] = is_string($value) ? trim($value) : $value;
            }
            $tahun = $row['tahun'] ?? null;
            // Spreadsheet libraries return numeric cells as integers/floats;
            // the period label is stored as text and should accept that form.
            $periodeLabel = $row['periode_label'] ?? $row['periode'] ?? (string) $tahun;
            if (is_int($periodeLabel) || is_float($periodeLabel)) {
                $periodeLabel = (string) $periodeLabel;
            }
            if ($tahun === null || $tahun === '' || $values === []) {
                throw new \InvalidArgumentException('Tahun dan minimal satu kolom values_* wajib diisi.');
            }
            $wilayahId = $row['wilayah_id'] ?? $row['kecamatan_id'] ?? null;
            $wilayahId = $wilayahId === '' ? null : $wilayahId;
            $wilayahType = $row['wilayah_type'] ?? ($wilayahId ? 'kecamatan' : 'kabupaten');
            if ($wilayahId) {
                foreach (['kecamatan', 'wilayah'] as $name) {
                    if (in_array($name, $fieldNames, true)) {
                        $values[$name] ??= $wilayahId;
                        break;
                    }
                }
            }

            DB::transaction(function () use ($tahun, $row, $wilayahId, $wilayahType, $values, $periodeLabel): void {
                $manager = app(DatasetValueManager::class);
                $value = $manager->createDraft($this->dataset, [
                    'dataset_id' => $this->dataset->id,
                    'tahun' => $tahun,
                    'periode_label' => $periodeLabel,
                    'wilayah_type' => $wilayahType,
                    'wilayah_id' => $wilayahId,
                    'values' => FieldAutoCalculator::apply($this->dataset, $values),
                ]);
                if ($this->autoPublish) {
                    $manager->submit($value);
                    $manager->verify($value, 'Bulk import auto-verified');
                    $manager->publish($value);
                }
            });
            $this->result['success']++;
        } catch (Throwable $exception) {
            $this->result['failed']++;
            $this->result['errors'][] = "Baris {$rowNumber}: ".$exception->getMessage();
        }
    }

    public static function getTemplateHeaders(Dataset $dataset): array
    {
        $headers = ['tahun', 'periode_label'];
        if ($dataset->cakupan_wilayah === 'kecamatan') {
            $headers[] = 'kecamatan_id';
        }
        foreach ($dataset->fields()->orderBy('urutan')->get() as $field) {
            $headers[] = 'values_' . $field->nama;
        }
        return $headers;
    }

    public static function getTemplateSample(Dataset $dataset): array
    {
        $sample = [2024, '2024'];
        if ($dataset->cakupan_wilayah === 'kecamatan') {
            $sample[] = 1;
        }
        foreach ($dataset->fields()->orderBy('urutan')->get() as $field) {
            $sample[] = match ($field->tipe) {
                'integer' => 0,
                'decimal' => 0.0,
                'boolean' => 'true',
                'date' => '2024-01-01',
                default => 'contoh',
            };
        }
        return $sample;
    }
}
