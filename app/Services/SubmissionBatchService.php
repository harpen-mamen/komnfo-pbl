<?php

namespace App\Services;

use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use App\Services\BatchApprovalService;
use Illuminate\Database\Eloquent\Model;
use App\Services\SubmissionSnapshotService;
use App\Services\SubmissionWorkflowService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SubmissionBatchService
{
    public const MODULE_RUMAH = SubmissionBatch::MODULE_RUMAH;

    public function __construct(
        private readonly SubmissionSnapshotService $snapshotService,
        private readonly SubmissionWorkflowService $workflowService,
    ) {
    }
    public const MODULE_KELUARGA = SubmissionBatch::MODULE_KELUARGA;
    public const MODULE_PENDUDUK = SubmissionBatch::MODULE_PENDUDUK;
    public const MODULE_KESEHATAN = SubmissionBatch::MODULE_KESEHATAN;
    public const MODULE_PENDIDIKAN = SubmissionBatch::MODULE_PENDIDIKAN;

    public const PACKAGE_KEPENDUDUKAN = 'kependudukan';
    public const PACKAGE_KESEHATAN = 'kesehatan';
    public const PACKAGE_PENDIDIKAN = 'pendidikan';

    public function moduleDefinitions(): array
    {
        return [
            self::MODULE_RUMAH => [
                'key' => self::MODULE_RUMAH,
                'label' => 'Rumah',
                'description' => 'Master data rumah yang dapat diperbarui kapan saja.',
                'period_type' => 'master',
                'badge' => 'Master data',
            ],
            self::MODULE_KELUARGA => [
                'key' => self::MODULE_KELUARGA,
                'label' => 'Keluarga',
                'description' => 'Master data kartu keluarga per kecamatan.',
                'period_type' => 'master',
                'badge' => 'Master data',
            ],
            self::MODULE_PENDUDUK => [
                'key' => self::MODULE_PENDUDUK,
                'label' => 'Penduduk',
                'description' => 'Master data penduduk per keluarga dan desa.',
                'period_type' => 'master',
                'badge' => 'Master data',
            ],
            self::MODULE_KESEHATAN => [
                'key' => self::MODULE_KESEHATAN,
                'label' => 'Kesehatan',
                'description' => 'Data kesehatan bulanan per puskesmas.',
                'period_type' => 'monthly',
                'badge' => 'Data periodik',
            ],
            self::MODULE_PENDIDIKAN => [
                'key' => self::MODULE_PENDIDIKAN,
                'label' => 'Pendidikan',
                'description' => 'Data pendidikan semesteran per sekolah.',
                'period_type' => 'semester',
                'badge' => 'Data periodik',
            ],
        ];
    }

    public function preview(int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null): array
    {
        $periodeBulan ??= now()->month;
        $semester ??= $this->defaultSemester();
        $periodeTahun ??= now()->year;

        return [
            'periode_bulan' => $periodeBulan,
            'semester' => $semester,
            'periode_tahun' => $periodeTahun,
            'modules' => collect($this->moduleDefinitions())
                ->map(fn (array $definition, string $module): array => $this->modulePreviewRow($module, $kecamatanId, $periodeBulan, $semester, $periodeTahun, $definition))
                ->values()
                ->all(),
        ];
    }

    public function submit(int $kecamatanId, array $modules = [], ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null, ?string $notes = null, ?User $actor = null): Collection
    {
        $actor ??= auth()->user();

        if (! $actor) {
            throw new \RuntimeException('Pengguna harus terautentikasi untuk mengirim batch.');
        }

        if ($actor->hasRole('admin_kecamatan') && (int) $actor->kecamatan_id !== $kecamatanId) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Admin Kecamatan tidak dapat mengirim data kecamatan lain.');
        }

        return DB::transaction(function () use ($kecamatanId, $modules, $periodeBulan, $semester, $periodeTahun, $notes, $actor): Collection {
            Kecamatan::query()->findOrFail($kecamatanId);
            $selectedModules = $this->normalizeModules($modules);

            if ($selectedModules === []) {
                throw new \RuntimeException('Pilih minimal satu modul data untuk dikirim.');
            }

            $modulePayloads = collect($selectedModules)
                ->map(function (string $module) use ($kecamatanId, $periodeBulan, $semester, $periodeTahun): array {
                    $context = $this->moduleContext($module, $periodeBulan, $semester, $periodeTahun);
                    $ids = $this->moduleQuery($module, $kecamatanId, $context['periode_bulan'], $context['semester'], $context['periode_tahun'])
                        ->pluck('id')
                        ->values();

                    return [
                        'module' => $module,
                        'context' => $context,
                        'ids' => $ids,
                        'count' => $ids->count(),
                    ];
                })
                ->filter(fn (array $payload): bool => $payload['count'] > 0)
                ->values();

            if ($modulePayloads->isEmpty()) {
                throw new \RuntimeException('Tidak ada data untuk dikirim pada modul yang dipilih.');
            }

            $modulePayloads->each(function (array $payload) use ($kecamatanId): void {
                $existingBatch = $this->activeBatchForModule(
                    $kecamatanId,
                    $payload['module'],
                    $payload['context']['periode_bulan'],
                    $payload['context']['semester'],
                    $payload['context']['periode_tahun'],
                );

                if ($existingBatch) {
                    throw new \RuntimeException('Sudah ada batch aktif untuk modul '.$this->moduleLabel($payload['module']).' pada kecamatan dan periode yang sama.');
                }
            });

            $modulesWithData = $modulePayloads->pluck('module')->all();
            $batchModule = count($modulesWithData) === 1 ? $modulesWithData[0] : SubmissionBatch::MODULE_MIXED;
            $primaryContext = $this->batchContext($modulePayloads);

            $batch = SubmissionBatch::create([
                'module' => $batchModule,
                'submission_number' => $this->generateSubmissionNumber($batchModule, $kecamatanId, $primaryContext['periode_bulan'], $primaryContext['semester'], $primaryContext['periode_tahun']),
                'kecamatan_id' => $kecamatanId,
                'periode_bulan' => $primaryContext['periode_bulan'],
                'semester' => $primaryContext['semester'],
                'periode_tahun' => $primaryContext['periode_tahun'],
                'package_signature' => $this->packageSignature($modulesWithData),
                'package_keys' => $modulesWithData,
                'package_summary' => $this->modulePackageSummary($modulePayloads),
                'status' => SubmissionBatch::STATUS_DRAFT,
                'notes' => $notes,
                'created_by' => $actor->id,
            ]);

            $moduleSnapshots = $this->moduleSnapshots($modulePayloads, $actor);
            $batch->payload_snapshot = count($modulesWithData) === 1
                ? $moduleSnapshots[0]
                : $this->snapshotService->buildMixedSnapshot($batch, $moduleSnapshots, $actor);

            $batch->version = 1;
            $batch->saveQuietly();

            $this->createMixedItems($batch, $modulePayloads);
            $this->snapshotService->logBatchSnapshot($batch, $actor);

            return collect([$this->workflowService->submit($batch, $actor, null)]);
        });
    }

    public function isLocked(Model $entity): bool
    {
        if (! method_exists($entity, 'latestSubmissionBatchItem')) {
            return false;
        }

        $entity->loadMissing(['latestSubmissionBatchItem.submissionBatch']);

        return in_array($entity->latestSubmissionBatchItem?->submissionBatch?->status, [
            SubmissionBatch::STATUS_PENDING,
            SubmissionBatch::STATUS_IN_REVIEW,
            SubmissionBatch::STATUS_APPROVED,
        ], true);
    }

    private function modulePreviewRow(string $module, int $kecamatanId, int $periodeBulan, string $semester, int $periodeTahun, array $definition): array
    {
        $context = $this->moduleContext($module, $periodeBulan, $semester, $periodeTahun);
        $count = $this->moduleCount($module, $kecamatanId, $context['periode_bulan'], $context['semester'], $context['periode_tahun']);
        $latestBatch = SubmissionBatch::query()
            ->forModule($module)
            ->where('kecamatan_id', $kecamatanId)
            ->forPeriod($context['periode_bulan'], $context['semester'], $context['periode_tahun'])
            ->latest('submitted_at')
            ->first();

        return [
            'key' => $module,
            'label' => $definition['label'],
            'description' => $definition['description'],
            'badge' => $definition['badge'],
            'period_type' => $definition['period_type'],
            'periode_bulan' => $context['periode_bulan'],
            'semester' => $context['semester'],
            'periode_tahun' => $context['periode_tahun'],
            'count' => $count,
            'latest_status' => $latestBatch?->status,
            'latest_status_label' => SubmissionBatch::statusLabel($latestBatch?->status),
            'latest_submission' => $latestBatch?->submitted_at?->format('d M Y H:i') ?? $latestBatch?->created_at?->format('d M Y H:i'),
        ];
    }

    private function moduleContext(string $module, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        $periodeTahun ??= now()->year;

        return match ($module) {
            self::MODULE_KESEHATAN => [
                'periode_bulan' => $periodeBulan ?? now()->month,
                'semester' => null,
                'periode_tahun' => $periodeTahun,
            ],
            self::MODULE_PENDIDIKAN => [
                'periode_bulan' => null,
                'semester' => $semester ?? $this->defaultSemester(),
                'periode_tahun' => $periodeTahun,
            ],
            default => [
                'periode_bulan' => null,
                'semester' => null,
                'periode_tahun' => null,
            ],
        };
    }

    private function moduleCount(string $module, int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null): int
    {
        return $this->moduleQuery($module, $kecamatanId, $periodeBulan, $semester, $periodeTahun)->count();
    }

    private function moduleQuery(string $module, int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null)
    {
        return match ($module) {
            self::MODULE_RUMAH => Rumah::query()->whereHas('desa', fn ($query) => $query->where('kecamatan_id', $kecamatanId)),
            self::MODULE_KELUARGA => Keluarga::query()->whereHas('rumah.desa', fn ($query) => $query->where('kecamatan_id', $kecamatanId)),
            self::MODULE_PENDUDUK => Penduduk::query()->whereHas('keluarga.rumah.desa', fn ($query) => $query->where('kecamatan_id', $kecamatanId)),
            self::MODULE_KESEHATAN => DataKesehatanBulanan::query()
                ->where('kecamatan_id', $kecamatanId)
                ->when($periodeBulan !== null, fn ($query) => $query->where('periode_bulan', $periodeBulan))
                ->when($periodeTahun !== null, fn ($query) => $query->where('periode_tahun', $periodeTahun)),
            self::MODULE_PENDIDIKAN => DataPendidikanBulanan::query()
                ->where('kecamatan_id', $kecamatanId)
                ->tap(fn ($query) => $this->applySemesterFilterOnTable($query, 'data_pendidikan_bulanans', $semester))
                ->when($periodeTahun !== null, fn ($query) => $query->where('periode_tahun', $periodeTahun)),
            default => throw new \InvalidArgumentException("Modul [{$module}] tidak dikenal."),
        };
    }

    private function moduleLabel(string $module): string
    {
        return SubmissionBatch::moduleLabel($module);
    }

    private function generateSubmissionNumber(string $module, int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null): string
    {
        $periodKey = match ($module) {
            self::MODULE_KESEHATAN => sprintf('B%02d-T%d', $periodeBulan ?? now()->month, $periodeTahun ?? now()->year),
            self::MODULE_PENDIDIKAN => sprintf('%s-T%d', strtoupper($semester ?? $this->defaultSemester()), $periodeTahun ?? now()->year),
            SubmissionBatch::MODULE_MIXED => sprintf('MIXED-T%d', $periodeTahun ?? now()->year),
            default => 'MASTER',
        };

        $prefix = sprintf('SB-%s-%s-%s', strtoupper($module), $kecamatanId, $periodKey);
        $sequence = SubmissionBatch::withTrashed()
            ->where('submission_number', 'like', "{$prefix}%")
            ->count() + 1;

        return sprintf('%s-%03d', $prefix, $sequence);
    }

    private function logBatchSnapshot(SubmissionBatch $batch, User $actor): void
    {
        try {
            \App\Models\AuditTrail::query()->create([
                'entity_type' => SubmissionBatch::class,
                'entity_id' => $batch->getKey(),
                'field' => 'submission_snapshot',
                'nilai_lama' => null,
                'nilai_baru' => json_encode($batch->payload_snapshot ?? [], JSON_UNESCAPED_UNICODE),
                'aksi' => 'create_batch',
                'pengguna_id' => $actor->id,
                'pengguna' => $actor->name,
                'role' => $actor->roles->pluck('name')->first(),
                'ip' => null,
                'browser' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write submission snapshot audit', ['error' => $e->getMessage()]);
        }
    }

    private function buildPackageMetrics(int $kecamatanId, string $semester, int $tahun): array
    {
        $rumahCount = Rumah::query()->whereHas('desa', fn ($query) => $query->where('kecamatan_id', $kecamatanId))->count();
        $keluargaCount = Keluarga::query()->whereHas('rumah.desa', fn ($query) => $query->where('kecamatan_id', $kecamatanId))->count();
        $pendudukCount = Penduduk::query()->whereHas('keluarga.rumah.desa', fn ($query) => $query->where('kecamatan_id', $kecamatanId))->count();

        $pendidikanQuery = DataPendidikanBulanan::query()->where('kecamatan_id', $kecamatanId)->where('periode_tahun', $tahun);
        $this->applySemesterFilterOnTable($pendidikanQuery, 'data_pendidikan_bulanans', $semester);
        $pendidikanCount = $pendidikanQuery->count();
        $sekolahCount = tap(
            DataPendidikanBulanan::query()->where('kecamatan_id', $kecamatanId)->where('periode_tahun', $tahun),
            fn ($query) => $this->applySemesterFilterOnTable($query, 'data_pendidikan_bulanans', $semester),
        )->distinct('sekolah')->count('sekolah');

        $kesehatanQuery = DataKesehatanBulanan::query()->where('kecamatan_id', $kecamatanId)->where('periode_tahun', $tahun);
        $this->applySemesterFilterOnTable($kesehatanQuery, 'data_kesehatan_bulanans', $semester);
        $kesehatanCount = $kesehatanQuery->count();
        $puskesmasCount = tap(
            DataKesehatanBulanan::query()->where('kecamatan_id', $kecamatanId)->where('periode_tahun', $tahun),
            fn ($query) => $this->applySemesterFilterOnTable($query, 'data_kesehatan_bulanans', $semester),
        )->distinct('puskesmas')->count('puskesmas');

        return [
            self::PACKAGE_KEPENDUDUKAN => [
                'key' => self::PACKAGE_KEPENDUDUKAN,
                'label' => 'Paket Kependudukan',
                'description' => 'Rumah, keluarga, dan penduduk dalam satu pengiriman.',
                'badge' => 'Data inti',
                'total' => $rumahCount + $keluargaCount + $pendudukCount,
                'items' => [
                    'Rumah' => [
                        'label' => 'Rumah',
                        'count' => $rumahCount,
                    ],
                    'Keluarga' => [
                        'label' => 'Keluarga',
                        'count' => $keluargaCount,
                    ],
                    'Penduduk' => [
                        'label' => 'Penduduk',
                        'count' => $pendudukCount,
                    ],
                ],
            ],
            self::PACKAGE_KESEHATAN => [
                'key' => self::PACKAGE_KESEHATAN,
                'label' => 'Paket Kesehatan',
                'description' => 'Laporan kesehatan bulanan dan puskesmas aktif.',
                'badge' => 'Data OPD',
                'total' => $kesehatanCount,
                'items' => [
                    'Laporan kesehatan' => [
                        'label' => 'Laporan kesehatan',
                        'count' => $kesehatanCount,
                    ],
                    'Puskesmas unik' => [
                        'label' => 'Puskesmas unik',
                        'count' => $puskesmasCount,
                    ],
                ],
            ],
            self::PACKAGE_PENDIDIKAN => [
                'key' => self::PACKAGE_PENDIDIKAN,
                'label' => 'Paket Pendidikan',
                'description' => 'Laporan pendidikan bulanan dan sekolah aktif.',
                'badge' => 'Data OPD',
                'total' => $pendidikanCount,
                'items' => [
                    'Laporan pendidikan' => [
                        'label' => 'Laporan pendidikan',
                        'count' => $pendidikanCount,
                    ],
                    'Sekolah unik' => [
                        'label' => 'Sekolah unik',
                        'count' => $sekolahCount,
                    ],
                ],
            ],
        ];
    }

    private function createItems(SubmissionBatch $batch, string $module, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): void
    {
        $rows = [];
        $ids = $this->moduleQuery($module, $batch->kecamatan_id, $periodeBulan, $semester, $periodeTahun)->pluck('id');
        $entityType = match ($module) {
            self::MODULE_RUMAH => Rumah::class,
            self::MODULE_KELUARGA => Keluarga::class,
            self::MODULE_PENDUDUK => Penduduk::class,
            self::MODULE_KESEHATAN => DataKesehatanBulanan::class,
            self::MODULE_PENDIDIKAN => DataPendidikanBulanan::class,
            default => null,
        };

        if (! $entityType || $ids->isEmpty()) {
            return;
        }

        $this->appendEntityRows($rows, $batch->id, $ids, $entityType, $module);

        SubmissionBatchItem::query()->insert($rows);
    }

    private function createMixedItems(SubmissionBatch $batch, Collection $modulePayloads): void
    {
        $rows = [];

        foreach ($modulePayloads as $payload) {
            $entityType = $this->entityTypeForModule($payload['module']);

            if (! $entityType) {
                continue;
            }

            $this->appendEntityRows($rows, $batch->id, $payload['ids'], $entityType, $payload['module']);
        }

        if ($rows !== []) {
            SubmissionBatchItem::query()->insert($rows);
        }
    }

    private function moduleSnapshots(Collection $modulePayloads, User $actor): array
    {
        return $modulePayloads
            ->map(fn (array $payload): array => [
                'module' => $payload['module'],
                'module_label' => SubmissionBatch::moduleLabel($payload['module']),
                'periode_bulan' => $payload['context']['periode_bulan'],
                'semester' => $payload['context']['semester'],
                'periode_tahun' => $payload['context']['periode_tahun'],
                'count' => $payload['count'],
                'entity_ids' => $payload['ids']->all(),
                'created_by' => $actor->id,
                'created_at' => now()->toDateTimeString(),
            ])
            ->values()
            ->all();
    }

    private function modulePackageSummary(Collection $modulePayloads): array
    {
        return $modulePayloads
            ->map(fn (array $payload): array => [
                'key' => $payload['module'],
                'label' => SubmissionBatch::moduleLabel($payload['module']),
                'total' => $payload['count'],
                'periode_bulan' => $payload['context']['periode_bulan'],
                'semester' => $payload['context']['semester'],
                'periode_tahun' => $payload['context']['periode_tahun'],
            ])
            ->values()
            ->all();
    }

    private function batchContext(Collection $modulePayloads): array
    {
        $contexts = $modulePayloads->pluck('context');

        return [
            'periode_bulan' => $contexts->pluck('periode_bulan')->filter(fn ($value) => $value !== null)->first(),
            'semester' => $contexts->pluck('semester')->filter(fn ($value) => $value !== null)->first(),
            'periode_tahun' => $contexts->pluck('periode_tahun')->filter(fn ($value) => $value !== null)->first(),
        ];
    }

    private function activeBatchForModule(int $kecamatanId, string $module, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): ?SubmissionBatch
    {
        return SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->whereIn('status', [
                SubmissionBatch::STATUS_DRAFT,
                SubmissionBatch::STATUS_PENDING,
                SubmissionBatch::STATUS_IN_REVIEW,
                SubmissionBatch::STATUS_REVISION,
            ])
            ->with('items')
            ->lockForUpdate()
            ->get()
            ->first(fn (SubmissionBatch $batch): bool => $this->batchContainsModule($batch, $module)
                && $this->batchMatchesModulePeriod($batch, $module, $periodeBulan, $semester, $periodeTahun));
    }

    private function batchContainsModule(SubmissionBatch $batch, string $module): bool
    {
        if ($batch->module === $module) {
            return true;
        }

        if (in_array($module, $batch->package_keys ?? [], true)) {
            return true;
        }

        return $batch->items->contains('package_key', $module);
    }

    private function batchMatchesModulePeriod(SubmissionBatch $batch, string $module, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): bool
    {
        $snapshot = $batch->payload_snapshot ?? [];
        $moduleSnapshot = collect($snapshot['modules'] ?? [])
            ->firstWhere('module', $module);

        if (is_array($moduleSnapshot)) {
            return $this->samePeriod($moduleSnapshot, $periodeBulan, $semester, $periodeTahun);
        }

        return $this->samePeriod([
            'periode_bulan' => $batch->periode_bulan,
            'semester' => $batch->semester,
            'periode_tahun' => $batch->periode_tahun,
        ], $periodeBulan, $semester, $periodeTahun);
    }

    private function samePeriod(array $period, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): bool
    {
        return ($period['periode_bulan'] ?? null) === $periodeBulan
            && ($period['semester'] ?? null) === $semester
            && ($period['periode_tahun'] ?? null) === $periodeTahun;
    }

    private function entityTypeForModule(string $module): ?string
    {
        return match ($module) {
            self::MODULE_RUMAH => Rumah::class,
            self::MODULE_KELUARGA => Keluarga::class,
            self::MODULE_PENDUDUK => Penduduk::class,
            self::MODULE_KESEHATAN => DataKesehatanBulanan::class,
            self::MODULE_PENDIDIKAN => DataPendidikanBulanan::class,
            default => null,
        };
    }

    private function appendEntityRows(array &$rows, int $batchId, Collection $entityIds, string $entityType, ?string $module = null): void
    {
        foreach ($entityIds as $entityId) {
            $rows[] = [
                'submission_batch_id' => $batchId,
                'package_key' => $module,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
    }

    private function normalizePackages(array $packages): array
    {
        $available = array_keys($this->moduleDefinitions());
        $normalized = collect($packages)
            ->map(fn ($package) => is_string($package) ? strtolower(trim($package)) : null)
            ->filter()
            ->unique()
            ->intersect($available)
            ->sort()
            ->values()
            ->all();

        return $normalized;
    }

    private function normalizeModules(array $modules): array
    {
        $available = array_keys($this->moduleDefinitions());

        return collect($modules)
            ->map(fn ($module) => is_string($module) ? strtolower(trim($module)) : null)
            ->filter()
            ->unique()
            ->intersect($available)
            ->sort()
            ->values()
            ->all();
    }

    private function packageSignature(array $packages): string
    {
        return implode('+', $this->normalizePackages($packages));
    }

    private function serializePackageSummary(array $metrics): array
    {
        return collect($metrics)->map(function (array $package): array {
            return [
                'key' => $package['key'],
                'label' => $package['label'],
                'description' => $package['description'],
                'badge' => $package['badge'],
                'total' => $package['total'],
                'items' => collect($package['items'])->map(fn (array $item): array => [
                    'label' => $item['label'],
                    'count' => $item['count'],
                ])->values()->all(),
            ];
        })->values()->all();
    }

    private function serializeSubmissionCounts(array $metrics): array
    {
        return collect($metrics)->mapWithKeys(fn (array $package): array => [
            $package['key'] => [
                'label' => $package['label'],
                'total' => $package['total'],
                'items' => collect($package['items'])->mapWithKeys(fn (array $item): array => [
                    $item['label'] => $item['count'],
                ])->all(),
            ],
        ])->all();
    }

    private function missingModules(array $counts): array
    {
        return Arr::where($counts, fn (int $count) => $count === 0);
    }

    private function legacyGenerateSubmissionNumber(int $kecamatanId, string $semester, int $tahun): string
    {
        $prefix = sprintf('SB-%s-%s-%s', $kecamatanId, strtoupper($semester), $tahun);
        $sequence = SubmissionBatch::query()
            ->where('submission_number', 'like', "{$prefix}%")
            ->count() + 1;

        return sprintf('%s-%03d', $prefix, $sequence);
    }

    private function defaultSemester(): string
    {
        return now()->month <= 6 ? 'ganjil' : 'genap';
    }

    /**
     * Apply semester filter to a query depending on whether the underlying table
     * has a `semester` column. For older tables without `semester`, map
     * semester 'ganjil'|'genap' to periode_bulan ranges (1-6 / 7-12).
     */
    private function applySemesterFilterOnTable($query, string $table, ?string $semester): void
    {
        if (empty($semester)) {
            return;
        }

        if (Schema::hasColumn($table, 'semester')) {
            $query->where('semester', $semester);

            return;
        }

        if ($semester === 'ganjil') {
            $query->whereBetween('periode_bulan', [1, 6]);
        } elseif ($semester === 'genap') {
            $query->whereBetween('periode_bulan', [7, 12]);
        }
    }
}
