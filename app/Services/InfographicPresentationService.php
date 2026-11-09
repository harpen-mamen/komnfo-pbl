<?php

namespace App\Services;

use App\Services\DataInput\RoadInputDefinition;
use App\Models\Dataset;
use Illuminate\Support\Collection;

/** Presentation only: does not modify records or infer unreported units. */
class InfographicPresentationService
{
    public static function number(int|float $value): string
    {
        $decimals = min(6, strlen(rtrim(explode('.', sprintf('%.6F', $value))[1], '0')));
        return number_format($value, $decimals, ',', '.');
    }

    public function context(Dataset $dataset, Collection $records): array
    {
        $notes = $records->pluck('notes')->filter()->map(fn ($note) => preg_replace('/^https?:\/\/\S+\s*/u', '', preg_replace('/^\[(?:SANGIHE-SOURCE:[^\]]+|STATISTIK-SEKTORAL-2024|PUBLIC-SOURCE:[^\]]+)\]\s*/', '', $note)))->unique()->values();
        $sectoral = $notes->contains(fn ($note) => str_contains($note, 'Statistik Sektoral'));
        $sourceMarker = $records->pluck('notes')->first(fn ($note) => str_starts_with((string) $note, '[PUBLIC-SOURCE:'));
        $sourceUrl = $sourceMarker ? trim(substr((string) $sourceMarker, strpos((string) $sourceMarker, ']') + 1)) : null;
        $sourceUrl = $sourceUrl ? (preg_split('/\s+/', $sourceUrl, 2)[0] ?? null) : null;
        return [
            'years' => $records->pluck('tahun')->filter()->unique()->sort()->values()->all(),
            'source' => data_get($dataset->config, 'source_publication') ?: ($sectoral ? 'Statistik Sektoral Sangihe — tahun data 2024' : 'Data terbit dari OPD; lihat catatan tiap data'),
            'source_url' => data_get($dataset->config, 'source_url') ?: ($sourceUrl ?: ($sectoral ? 'https://sangihekab.go.id/statistik-sektoral-tahun-data-2024/' : null)),
            'source_table' => data_get($dataset->config, 'source_table') ?: ($sectoral ? $dataset->kode : null),
            'source_page' => data_get($dataset->config, 'source_page') ?: ((string)$dataset->kode === '5.59' ? 110 : null),
            'notes' => $notes->all(),
            'is_location_reference' => str_starts_with((string)$dataset->kode, 'LOKASI_'),
        ];
    }

    public function describeCharts(Dataset $dataset, Collection $records, array $charts): array
    {
        return array_map(function ($chart) use ($dataset, $records) {
            $terms = [
                'AKI'=>'Angka Kematian Ibu (AKI)', 'AKB'=>'Angka Kematian Bayi (AKB)',
                'AHH'=>'Angka Harapan Hidup (AHH)', 'SPBE'=>'Sistem Pemerintahan Berbasis Elektronik (SPBE)',
                'TPT'=>'Tingkat Pengangguran Terbuka (TPT)', 'TPAK'=>'Tingkat Partisipasi Angkatan Kerja (TPAK)',
            ];
            $chart['labels'] = array_map(fn ($label) => $terms[strtoupper(trim($label))] ?? $label, $chart['labels']);
            $unit = trim((string)($chart['unit'] ?? ''));
            if (preg_match('/^(numerik|teks|km\/unit\/m|per indikator|tanpa satuan)$/i', $unit)) $unit = '';
            $title = trim((string)$chart['title']);
            $title = preg_replace('/\s+\('.preg_quote($unit,'/').'\)$/u', '', $title);
            if (preg_match('/^(nilai|nilai \([^)]*\)|jumlah|total)$/iu', $title)) {
                $title = count($chart['labels']) === 1 ? (string)$chart['labels'][0] : $dataset->nama;
            }
            $single = count($chart['values']) === 1;
            $chart['title'] = $terms[strtoupper(trim($title))] ?? $title;
            $chart['definition'] = $dataset->fields->firstWhere('nama', $chart['metric'] ?? '')?->definisi;
            $chart['unit'] = $unit;
            $chart['years'] = $records->pluck('tahun')->filter()->unique()->sort()->values()->all();
            $chart['description'] = $single ? 'Angka untuk indikator ini sesuai data yang diterbitkan.' : 'Bandingkan angka pada setiap rincian. Pilih tampilan grafik atau buka tabel untuk membaca angka lengkap.';
            $chart['caution'] = $unit === '' ? 'Satuan belum dicantumkan pada metadata. Jangan menafsirkan angka ini sebagai persen, panjang, atau jumlah orang sebelum satuannya dikonfirmasi.' : null;
            if (($chart['type'] ?? '') === 'histogram') {
                $chart['description'] = 'Jumlah catatan pada setiap rentang angka; bukan nilai tiap objek. Rentang dalam satuan '.($unit ?: 'yang belum dicantumkan').'.';
                $chart['unit'] = 'catatan';
            }
            if (($chart['grouping'] ?? '') === 'time') $chart['description'] = 'Perubahan angka antarperiode. Setiap titik menunjukkan periode yang tertulis, bukan penjumlahan seluruh tahun.';
            $chart['aggregation_note'] = 'Angka kosong tidak diubah menjadi nol. '.(($chart['grouping'] ?? '') === 'time' ? '' : 'Jika ada beberapa catatan pada rincian yang sama, angka mengikuti pengelompokan dataset; lihat tabel sumber untuk detail.');
            return $chart;
        }, $charts);
    }

    public function roadCharts(Dataset $dataset, Collection $records): array
    {
        $records = $records->map(function ($record) {
            $copy=clone $record;
            $copy->values=RoadInputDefinition::normalize($record->values??[]);
            return $copy;
        });
        $charts = [];
        foreach ($records->groupBy('tahun') as $year => $rows) {
            $specs = [
                ['Panjang jalan menurut kewenangan', 'km', ['Jalan Kabupaten','Jalan Provinsi','Jalan Nasional'], null, 'Panjang jalan kabupaten, provinsi, dan nasional adalah kelompok kewenangan yang berbeda.'],
                ['Permukaan jalan kabupaten', 'km', ['Konstruksi Permukaan'], null, 'Rincian panjang jalan kabupaten menurut bahan permukaannya. Ini bagian dari jalan kabupaten, bukan tambahan panjang jalan.'],
                ['Kondisi jalan kabupaten', 'km', ['Kualifikasi Kondisi'], null, 'Rincian panjang jalan kabupaten menurut kondisinya. Jangan dijumlahkan lagi dengan rincian permukaan jalan.'],
                ['Jumlah jembatan', 'unit', ['Jembatan'], '(unit)', 'Jumlah bangunan jembatan yang dilaporkan, bukan panjang jembatan.'],
                ['Panjang keseluruhan jembatan', 'm', ['Jembatan'], '(m)', 'Total panjang jembatan dalam meter. Angka ini tidak boleh dijumlahkan dengan jumlah unit jembatan.'],
            ];
            foreach ($specs as [$title,$unit,$categories,$suffix,$description]) {
                $selected = $rows->filter(fn ($row) => in_array($row->values['kategori']??'', $categories, true)
                    && (!$suffix || str_ends_with(trim($row->values['uraian']??''), $suffix)) && is_numeric($row->values['nilai']??null))->values();
                if ($selected->isEmpty()) continue;
                $charts[] = [
                    'title'=>$title, 'type'=>'bar', 'unit'=>$unit, 'years'=>[$year], 'grouping'=>'category',
                    'labels'=>$selected->map(fn($row)=>preg_replace('/\s*\((km|m|unit)\)\s*$/i','',$row->values['uraian']))->all(),
                    'values'=>$selected->map(fn($row)=>(float)$row->values['nilai'])->all(),
                    'description'=>$description, 'caution'=>null,
                    'aggregation_note'=>((int)$year === 2024 ? 'Sumber: Dinas PUPR Daerah, Statistik Sektoral 2024, tabel 5.59, halaman 110. ' : 'Lihat sumber dan catatan dataset untuk tahun ini. ').'Nilai disajikan terpisah sesuai uraian asli; tidak dijumlahkan lintas satuan.',
                ];
            }
        }
        return $charts;
    }
}
