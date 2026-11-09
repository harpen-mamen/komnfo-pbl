<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class PublicWeatherController extends Controller
{
    /** Return BMKG's latest village forecast for the Sangihe capital, Tahuna. */
    public function index(): JsonResponse
    {
        try {
            $body = Cache::remember('public.weather.raw.manente.v1', now()->addMinutes(30), function (): array {
                try {
                    $response = Http::acceptJson()
                        ->withOptions(['verify' => storage_path('app/bmkg-cacert.pem')])
                        ->timeout(8)
                        ->retry(1, 200)
                        ->get('https://api.bmkg.go.id/publik/prakiraan-cuaca', [
                            'adm4' => '71.03.17.1017', // Kelurahan Manente, Kecamatan Tahuna
                        ]);

                    $response->throw();
                    $fresh = $response->json();
                    abort_unless(is_array($fresh) && ! empty($fresh['lokasi']) && ! empty($fresh['data']), 502, 'Format prakiraan BMKG tidak dikenali.');

                    Cache::put('public.weather.raw.stale.manente.v1', $fresh, now()->addDays(3));
                    return $fresh;
                } catch (Throwable $exception) {
                    report($exception);
                    $cached = Cache::get('public.weather.raw.stale.manente.v1');
                    if (is_array($cached)) return $cached;

                    $snapshotPath = storage_path('app/bmkg-cuaca-manente.json');
                    abort_unless(is_file($snapshotPath), 503, 'Data prakiraan BMKG belum tersedia.');
                    $snapshotContents = (string) file_get_contents($snapshotPath);
                    $snapshotContents = preg_replace('/^\xEF\xBB\xBF/', '', $snapshotContents) ?? $snapshotContents;
                    $snapshot = json_decode($snapshotContents, true);
                    abort_unless(is_array($snapshot), 503, 'Cache prakiraan BMKG tidak valid.');
                    return $snapshot;
                }
            });

            $location = data_get($body, 'data.0.lokasi', data_get($body, 'lokasi', []));
            $forecastGroups = data_get($body, 'data.0.cuaca', data_get($body, 'data', []));
            $forecasts = collect($forecastGroups)->flatten(1)
                ->filter(fn ($item) => is_array($item) && isset($item['datetime']))
                ->sortBy(fn (array $item) => $item['utc_datetime'] ?? $item['datetime'])
                ->values();

            $current = $forecasts->first(fn (array $item) => now('UTC')->lte(\Illuminate\Support\Carbon::parse($item['utc_datetime'] ?? $item['datetime'], 'UTC')))
                ?? $forecasts->last();
            abort_unless($current, 502, 'Data prakiraan BMKG belum tersedia.');

            $timezone = data_get($location, 'timezone', 'Asia/Makassar');
            $payload = [
                'location' => [
                    'village' => data_get($location, 'desa', 'Manente'),
                    'district' => data_get($location, 'kecamatan', 'Tahuna'),
                    'regency' => data_get($location, 'kotkab', 'Kepulauan Sangihe'),
                    'timezone' => $timezone,
                ],
                'forecast' => [
                    'datetime' => $current['local_datetime'] ?? \Illuminate\Support\Carbon::parse($current['utc_datetime'] ?? $current['datetime'], 'UTC')->setTimezone($timezone)->format('Y-m-d H:i:s'),
                    'description' => $current['weather_desc'] ?? 'Kondisi tidak tersedia',
                    'temperature' => isset($current['t']) ? (float) $current['t'] : null,
                    'humidity' => isset($current['hu']) ? (int) $current['hu'] : null,
                    'wind_speed' => isset($current['ws']) ? (float) $current['ws'] : null,
                    'wind_direction' => $current['wd_to'] ?? $current['wd'] ?? null,
                    'icon' => $current['image'] ?? null,
                ],
                'updated_at' => $current['analysis_date'] ?? null,
                'source' => 'BMKG',
            ];

            return response()->json($payload)->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=600');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Informasi cuaca BMKG sementara tidak dapat dimuat.',
                'source' => 'BMKG',
            ], 503)->header('Cache-Control', 'public, max-age=60');
        }
    }
}
