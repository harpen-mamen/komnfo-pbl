<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicStatisticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payload = Cache::remember('public.statistics.v2', now()->addMinutes(5), function (): array {
            $values = DatasetValue::query()->where('status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('published_at')
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))
                ->whereHas('dataset', fn ($query) => $query->where('is_active', true)
                    ->whereIn('lisensi', [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN])->whereIn('kode', ['3.12', '3.13'])
                    ->whereHas('opd', fn ($opd) => $opd->where('kode', 'DUKCAPIL')))
                ->with('dataset:id,kode,kode_sdi,nama')->get();
            $populationValues = $values->filter(fn (DatasetValue $value) => in_array((string) ($value->dataset?->kode_sdi ?: $value->dataset?->kode), ['3.13'], true));
            $currentYear = $populationValues->max('tahun');
            $population = $this->sumValues($values, $currentYear, ['penduduk_laki', 'penduduk_perempuan', 'total']);
            $households = $this->sumValues($values, $currentYear, ['kk_laki', 'kk_perempuan', 'total']);

            return [
                'total' => ['penduduk' => $population, 'keluarga' => $households],
                'by_gender' => [],
                'by_age' => [],
                'diseases' => [],
                'tahun' => $currentYear,
                'catatan' => 'Ringkasan hanya memuat agregat dari dataset terbuka yang telah diterbitkan.',
            ];
        });
        return response()->json($payload);
    }

    private function sumValues($values, $year, array $fields): int|float
    {
        if ($year === null) return 0;
        return $values->filter(function (DatasetValue $value) use ($year, $fields) {
            $code = (string) ($value->dataset?->kode_sdi ?: $value->dataset?->kode);
            $expectedCode = in_array('kk_laki', $fields, true) ? '3.12' : '3.13';
            return $value->tahun === $year && $code === $expectedCode && $value->wilayah_type === Wilayah::TIPE_KECAMATAN;
        })->sum(function (DatasetValue $value) use ($fields) {
            foreach ($fields as $field) {
                $number = data_get($value->values, $field);
                if (is_numeric($number)) return (float) $number;
            }
            return 0;
        });
    }
}
