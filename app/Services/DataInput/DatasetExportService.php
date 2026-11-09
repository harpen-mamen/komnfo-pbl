<?php

namespace App\Services\DataInput;

use App\Models\Dataset;
use App\Models\DatasetValue;
use Illuminate\Support\Collection;

class DatasetExportService
{
    /**
     * Export dataset values to CSV format.
     */
    public function toCsv(Dataset $dataset, ?string $status = null): string
    {
        $values = $this->queryValues($dataset, $status)->get();
        $fields = $dataset->fields()->orderBy('urutan')->get();

        $headers = ['id', 'tahun', 'periode_label', 'wilayah_type', 'wilayah_id', 'status'];
        foreach ($fields as $f) {
            $headers[] = $f->nama;
        }
        $headers[] = 'created_at';
        $headers[] = 'verified_at';
        $headers[] = 'published_at';

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);

        foreach ($values as $v) {
            $row = [
                $v->id,
                $v->tahun,
                $v->periode_label ?? '',
                $v->wilayah_type ?? '',
                $v->wilayah_id ?? '',
                $v->status ?? '',
            ];
            foreach ($fields as $f) {
                $row[] = $this->exportCell($v->values[$f->nama] ?? '');
            }
            $row[] = $v->created_at?->format('Y-m-d H:i:s');
            $row[] = $v->verified_at?->format('Y-m-d H:i:s');
            $row[] = $v->published_at?->format('Y-m-d H:i:s');
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    /**
     * Export to JSON.
     */
    public function toJson(Dataset $dataset, ?string $status = null): string
    {
        $values = $this->queryValues($dataset, $status)->get();
        $data = [
            'dataset' => [
                'kode' => $dataset->kode,
                'nama' => $dataset->nama,
                'opd' => $dataset->opd?->nama,
                'period_type' => $dataset->period_type,
            ],
            'exported_at' => now()->toIso8601String(),
            'total' => $values->count(),
            'values' => $values->map(fn ($v) => [
                'id' => $v->id,
                'tahun' => $v->tahun,
                'periode_label' => $v->periode_label,
                'wilayah_id' => $v->wilayah_id,
                'status' => $v->status,
                'values' => $v->values,
                'created_at' => $v->created_at?->toIso8601String(),
                'verified_at' => $v->verified_at?->toIso8601String(),
                'published_at' => $v->published_at?->toIso8601String(),
            ])->all(),
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generate XLSX-like HTML that Excel can open.
     */
    public function toExcelHtml(Dataset $dataset, ?string $status = null): string
    {
        $values = $this->queryValues($dataset, $status)->get();
        $fields = $dataset->fields()->orderBy('urutan')->get();

        $html = '<html><head><meta charset="utf-8"><title>' . $dataset->nama . '</title></head><body>';
        $html .= '<table border="1" cellspacing="0" cellpadding="4">';
        $html .= '<thead><tr style="background: #4f46e5; color: white;">';
        $html .= '<th>ID</th><th>Tahun</th><th>Periode</th><th>Wilayah</th><th>Status</th>';
        foreach ($fields as $f) {
            $html .= '<th>' . htmlspecialchars($f->label) . '</th>';
        }
        $html .= '<th>Tgl Input</th></tr></thead><tbody>';

        foreach ($values as $v) {
            $html .= '<tr>';
            $html .= '<td>' . $v->id . '</td>';
            $html .= '<td>' . $v->tahun . '</td>';
            $html .= '<td>' . ($v->periode_label ?? '-') . '</td>';
            $html .= '<td>' . ($v->wilayah_id ?? '-') . '</td>';
            $html .= '<td>' . $v->status . '</td>';
            foreach ($fields as $f) {
                $val = $v->values[$f->nama] ?? '';
                $html .= '<td>' . htmlspecialchars($this->exportCell($val), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '<td>' . ($v->created_at?->format('Y-m-d H:i') ?? '-') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';
        return $html;
    }

    /**
     * Build query.
     */
    private function exportCell(mixed $value): string
    {
        if (is_array($value) || is_object($value)) return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $text = (string) ($value ?? '');
        // Prevent public text fields being interpreted as spreadsheet formulas.
        return preg_match('/^[=+@\t\r]/', $text) || (str_starts_with($text, '-') && !is_numeric($text)) ? "'".$text : $text;
    }

    protected function queryValues(Dataset $dataset, ?string $status = null)
    {
        $query = DatasetValue::where('dataset_id', $dataset->id)->orderByDesc('created_at');
        if ($status) $query->where('status', $status);
        return $query;
    }

    /**
     * Get aggregated stats per dataset (for chart).
     */
    public function getStatusBreakdown(Dataset $dataset): array
    {
        $stats = DatasetValue::where('dataset_id', $dataset->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        return [
            'draft' => $stats['draft'] ?? 0,
            'submitted' => $stats['submitted'] ?? 0,
            'verified' => $stats['verified'] ?? 0,
            'published' => $stats['published'] ?? 0,
            'rejected' => $stats['rejected'] ?? 0,
            'revision' => $stats['revision'] ?? 0,
        ];
    }

    /**
     * Get time series aggregation.
     */
    public function getTimeSeries(Dataset $dataset, ?string $fieldName = null): Collection
    {
        $values = DatasetValue::where('dataset_id', $dataset->id)
            ->where('status', 'published')
            ->orderBy('tahun')
            ->orderBy('periode_label')
            ->get();

        return $values->groupBy(fn ($v) => $v->tahun . '-' . ($v->periode_label ?? 'Tahunan'))
            ->map(fn ($group) => [
                'periode' => $group->first->tahun . '-' . ($group->first->periode_label ?? 'Tahunan'),
                'total' => $group->count(),
                'sum_values' => $fieldName
                    ? $group->sum(fn ($v) => (float) ($v->values[$fieldName] ?? 0))
                    : null,
            ])
            ->values();
    }
}
