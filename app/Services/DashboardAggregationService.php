<?php

namespace App\Services;

use App\Models\ImportPendudukLog;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAggregationService
{
    public function kecamatanRows(): Collection
    {
        $kecamatans = Kecamatan::query()
            ->with('desas:id,kecamatan_id,nama')
            ->orderBy('nama')
            ->get();

        if ($kecamatans->isEmpty()) {
            return collect();
        }

        $rumahCounts = $this->rumahCounts();
        $keluargaCounts = $this->keluargaCounts();
        $pendudukCounts = $this->pendudukCounts();
        $rumahBelumLengkap = $this->rumahBelumLengkapCounts();
        $keluargaBelumLengkap = $this->keluargaBelumLengkapCounts();
        $pendudukBelumLengkap = $this->pendudukBelumLengkapCounts();
        $importGagal = $this->importGagalCounts();
        $latestActivity = $this->latestActivityPerKecamatan();

        return $kecamatans->map(function (Kecamatan $kecamatan) use (
            $rumahCounts, $keluargaCounts, $pendudukCounts,
            $rumahBelumLengkap, $keluargaBelumLengkap, $pendudukBelumLengkap,
            $importGagal, $latestActivity
        ): array {
            $rumahTotal = (int) ($rumahCounts[$kecamatan->id] ?? 0);
            $keluargaTotal = (int) ($keluargaCounts[$kecamatan->id] ?? 0);
            $pendudukTotal = (int) ($pendudukCounts[$kecamatan->id] ?? 0);
            $progress = $this->overallProgress($rumahTotal, $rumahTotal, $keluargaTotal, $keluargaTotal, $pendudukTotal, $pendudukTotal);

            return [
                'id' => $kecamatan->id,
                'nama' => $kecamatan->nama,
                'desa_total' => $kecamatan->desas->count(),
                'rumah_total' => $rumahTotal,
                'keluarga_total' => $keluargaTotal,
                'penduduk_total' => $pendudukTotal,
                'rumah_selesai' => $rumahTotal,
                'keluarga_selesai' => $keluargaTotal,
                'penduduk_selesai' => $pendudukTotal,
                'rumah_belum_lengkap' => (int) ($rumahBelumLengkap[$kecamatan->id] ?? 0),
                'keluarga_belum_lengkap' => (int) ($keluargaBelumLengkap[$kecamatan->id] ?? 0),
                'penduduk_belum_lengkap' => (int) ($pendudukBelumLengkap[$kecamatan->id] ?? 0),
                'import_gagal' => (int) ($importGagal[$kecamatan->id] ?? 0),
                'latest_activity' => $latestActivity[$kecamatan->id] ?? null,
                'progress' => $progress,
                'status' => $this->statusLabel($progress),
                'color' => $this->statusColor($progress),
            ];
        });
    }

    private function approvedSubquery(): string
    {
        // Get IDs of approved submission batches
        $approvedBatchIds = DB::table('submission_batches')
            ->where('status', 'disetujui')
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        if (empty($approvedBatchIds)) {
            return '0';
        }

        // Get latest approved batch ID per entity using a correlated subquery
        return DB::table('submission_batch_items as sbi')
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->whereIn('sbi.submission_batch_id', $approvedBatchIds)
            ->whereNull('sb.deleted_at')
            ->selectRaw('MAX(sbi.submission_batch_id)')
            ->toString();
    }

    private function rumahCounts(): array
    {
        return DB::table('rumah')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'rumah.id')
                    ->where('sbi.entity_type', Rumah::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->selectRaw('desas.kecamatan_id as kecamatan_id, COUNT(rumah.id) as total')
            ->groupBy('desas.kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function keluargaCounts(): array
    {
        return DB::table('keluarga')
            ->join('rumah', 'keluarga.rumah_id', '=', 'rumah.id')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'keluarga.id')
                    ->where('sbi.entity_type', Keluarga::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->selectRaw('desas.kecamatan_id as kecamatan_id, COUNT(keluarga.id) as total')
            ->groupBy('desas.kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function pendudukCounts(): array
    {
        return DB::table('penduduk')
            ->join('keluarga', 'penduduk.keluarga_id', '=', 'keluarga.id')
            ->join('rumah', 'keluarga.rumah_id', '=', 'rumah.id')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'penduduk.id')
                    ->where('sbi.entity_type', Penduduk::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->selectRaw('desas.kecamatan_id as kecamatan_id, COUNT(penduduk.id) as total')
            ->groupBy('desas.kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function rumahBelumLengkapCounts(): array
    {
        return DB::table('rumah')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'rumah.id')
                    ->where('sbi.entity_type', Rumah::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->where(function ($q) {
                $q->whereNull('rumah.latitude')
                    ->orWhereNull('rumah.longitude')
                    ->orWhere('rumah.latitude', 0)
                    ->orWhere('rumah.longitude', 0);
            })
            ->selectRaw('desas.kecamatan_id as kecamatan_id, COUNT(rumah.id) as total')
            ->groupBy('desas.kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function keluargaBelumLengkapCounts(): array
    {
        return DB::table('keluarga')
            ->join('rumah', 'keluarga.rumah_id', '=', 'rumah.id')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'keluarga.id')
                    ->where('sbi.entity_type', Keluarga::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->where(function ($q) {
                $q->whereNotExists(function ($penduduk) {
                    $penduduk->selectRaw('1')
                        ->from('penduduk')
                        ->whereColumn('penduduk.keluarga_id', 'keluarga.id');
                })
                    ->orWhereNull('keluarga.jumlah_anggota')
                    ->orWhere('keluarga.jumlah_anggota', '<=', 0);
            })
            ->selectRaw('desas.kecamatan_id as kecamatan_id, COUNT(keluarga.id) as total')
            ->groupBy('desas.kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function pendudukBelumLengkapCounts(): array
    {
        return DB::table('penduduk')
            ->join('keluarga', 'penduduk.keluarga_id', '=', 'keluarga.id')
            ->join('rumah', 'keluarga.rumah_id', '=', 'rumah.id')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'penduduk.id')
                    ->where('sbi.entity_type', Penduduk::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->where(function ($q) {
                $q->whereNull('penduduk.nik')
                    ->orWhere('penduduk.nik', '')
                    ->orWhereNull('penduduk.pendidikan')
                    ->orWhere('penduduk.pendidikan', '')
                    ->orWhereNull('penduduk.pekerjaan')
                    ->orWhere('penduduk.pekerjaan', '');
            })
            ->selectRaw('desas.kecamatan_id as kecamatan_id, COUNT(penduduk.id) as total')
            ->groupBy('desas.kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function importGagalCounts(): array
    {
        return ImportPendudukLog::query()
            ->selectRaw('kecamatan_id, COUNT(*) as total')
            ->groupBy('kecamatan_id')
            ->pluck('total', 'kecamatan_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function latestActivityPerKecamatan(): array
    {
        // Get max created_at per entity type grouped by kecamatan, then get overall max
        $rumahLatest = DB::table('rumah')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'rumah.id')
                    ->where('sbi.entity_type', Rumah::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->selectRaw('desas.kecamatan_id, MAX(rumah.created_at) as latest')
            ->groupBy('desas.kecamatan_id');

        $keluargaLatest = DB::table('keluarga')
            ->join('rumah', 'keluarga.rumah_id', '=', 'rumah.id')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'keluarga.id')
                    ->where('sbi.entity_type', Keluarga::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->selectRaw('desas.kecamatan_id, MAX(keluarga.created_at) as latest')
            ->groupBy('desas.kecamatan_id');

        $pendudukLatest = DB::table('penduduk')
            ->join('keluarga', 'penduduk.keluarga_id', '=', 'keluarga.id')
            ->join('rumah', 'keluarga.rumah_id', '=', 'rumah.id')
            ->join('desas', 'rumah.desa_id', '=', 'desas.id')
            ->join('submission_batch_items as sbi', function ($join) {
                $join->on('sbi.entity_id', '=', 'penduduk.id')
                    ->where('sbi.entity_type', Penduduk::class);
            })
            ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
            ->where('sb.status', 'disetujui')
            ->whereNull('sb.deleted_at')
            ->selectRaw('desas.kecamatan_id, MAX(penduduk.created_at) as latest')
            ->groupBy('desas.kecamatan_id');

        $rows = DB::query()
            ->fromSub($rumahLatest->unionAll($keluargaLatest)->unionAll($pendudukLatest), 'combined')
            ->selectRaw('kecamatan_id, MAX(latest) as latest_activity')
            ->groupBy('kecamatan_id')
            ->pluck('latest_activity', 'kecamatan_id');

        return $rows->map(fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v) : null)->all();
    }

    private function overallProgress(int $rumahDone, int $rumahTotal, int $keluargaDone, int $keluargaTotal, int $pendudukDone, int $pendudukTotal): int
    {
        $items = [
            ['done' => $rumahDone, 'total' => $rumahTotal],
            ['done' => $keluargaDone, 'total' => $keluargaTotal],
            ['done' => $pendudukDone, 'total' => $pendudukTotal],
        ];

        $percents = collect($items)->map(fn (array $item): int => $this->percent((int) $item['done'], (int) $item['total']))->all();

        return (int) round(collect($percents)->avg());
    }

    private function percent(int $done, int $total): int
    {
        return $total > 0 ? (int) round(($done / $total) * 100) : 0;
    }

    private function statusLabel(int $percent): string
    {
        return match (true) {
            $percent >= 80 => 'Aman',
            $percent >= 50 => 'Perlu Perhatian',
            default => 'Mendesak',
        };
    }

    private function statusColor(int $percent): string
    {
        return match (true) {
            $percent >= 80 => 'success',
            $percent >= 50 => 'warning',
            default => 'danger',
        };
    }
}
