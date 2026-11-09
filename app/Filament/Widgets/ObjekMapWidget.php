<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB as FacadesDB;

/**
 * ObjekMapWidget — Mini Leaflet map + top-5 desa leaderboard.
 *
 * Tampilkan top 100 objek PUBLISHED dengan geometry dari MySQL via ST_AsGeoJSON().
 * Top 5 desa dengan objek published terbanyak di leaderboard samping.
 */
class ObjekMapWidget extends Widget
{
    protected string $view = 'filament.widgets.objek-map';
    protected int|string|array $columnSpan = 6;

    /** @var array<int,array{id:int,jenis:string,kondisi:?string,lat:float,lng:float}> */
    public array $markers = [];

    /** @var array<int,array{nama:string,total:int}> */
    public array $topDesa = [];

    public function mount(): void
    {
        // Markers: top 100 published objek dengan koordinat valid
        $rows = FacadesDB::table('objek_lapangan')
            ->selectRaw('id, jenis, kondisi, ST_AsGeoJSON(geometri) AS geo, unit_id')
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $this->markers = $rows->map(function ($r) {
            $g = json_decode($r->geo ?? 'null', true);
            return [
                'id'       => (int) $r->id,
                'jenis'    => (string) $r->jenis,
                'kondisi'   => $r->kondisi,
                'unit_id'   => (int) $r->unit_id,
                'lat'       => $g['coordinates'][1] ?? null,
                'lng'       => $g['coordinates'][0] ?? null,
            ];
        })
        ->filter(fn ($m) => $m['lat'] !== null && $m['lng'] !== null)
        ->values()
        ->toArray();

        // Top 5 desa
        $this->topDesa = Unit::where('tipe', 'desa')
            ->withCount(['objekLapangans' => fn ($q) => $q->where('status', 'published')])
            ->orderByDesc('objek_lapangans_count')
            ->limit(5)
            ->get(['id', 'nama'])
            ->map(fn ($d) => ['nama' => $d->nama, 'total' => (int) $d->objek_lapangans_count])
            ->toArray();
    }
}
