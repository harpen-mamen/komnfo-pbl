<?php

namespace App\Support;

use App\Models\Opd;
use App\Models\Dataset;

class OpdCatalog
{
    /**
     * Ambil semua OPD aktif dari database dengan dataset count
     */
    public static function all(bool $includeDatasetCounts = true): array
    {
        $datasets = Dataset::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->when($includeDatasetCounts, fn ($query) => $query->withCount(['fields', 'values']))
            ->orderBy('nama')
            ->get(['id', 'opd_id', 'nama', 'kode']);
        $datasetsByOpd = $datasets->groupBy('opd_id');

        return Opd::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get()
            ->map(function ($opd) use ($datasetsByOpd) {
                $opdDatasets = $datasetsByOpd->get($opd->id, collect());

                return [
                    'id' => $opd->id,
                    'name' => $opd->nama,
                    'short' => $opd->singkatan ?? substr($opd->nama, 0, 10),
                    'kategori' => $opd->kategori,
                    'datasets' => $opdDatasets->map(fn ($ds) => [
                        'id' => $ds->id,
                        'label' => $ds->nama,
                        'kode' => $ds->kode,
                        'fields_count' => $ds->fields_count ?? 0,
                        'values_count' => $ds->values_count ?? 0,
                    ])->values()->all(),
                ];
            })->all();
    }

    /**
     * Cari OPD berdasarkan key (slug/nama)
     */
    public static function find(string $key): ?array
    {
        $opds = self::all();
        // Search by id
        foreach ($opds as $opd) {
            if ((string)$opd['id'] === $key) {
                return $opd;
            }
        }
        return null;
    }

    /**
     * OPD yang visible untuk user saat ini
     */
    public static function visibleToCurrentUser(bool $includeDatasetCounts = true): array
    {
        $user = auth()->user();
        $opds = self::all($includeDatasetCounts);

        // Jika tidak ada user yang login, kembalikan semua OPD
        if (!$user) {
            return $opds;
        }

        // Super admin dan admin bisa lihat semua
        if ($user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo', 'walidata'])) {
            return $opds;
        }

        // User dengan opd_id spesifik hanya lihat OPD-nya
        if ($user->opd_id) {
            return array_values(array_filter($opds, fn($opd) => $opd['id'] === $user->opd_id));
        }

        return [];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * OPD yang punya dataset
     */
    public static function withDatasets(): array
    {
        return array_filter(self::all(), fn (array $opd): bool => count($opd['datasets']) > 0);
    }

    /**
     * OPD yang belum punya dataset
     */
    public static function withoutDatasets(): array
    {
        return array_filter(self::all(), fn (array $opd): bool => empty($opd['datasets']));
    }
}
