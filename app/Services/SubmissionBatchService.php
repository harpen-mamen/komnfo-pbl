<?php

namespace App\Services;

use RuntimeException;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Rumah;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\StokObat;
use App\Models\RekamMedis;
use App\Models\DataGuru;
use App\Models\DataSiswa;
use App\Models\DataPrasarana;
use App\Models\Wilayah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use App\Services\Submission\Modules\ModuleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SubmissionBatchService � facade for batch creation.
 *
 * Sprint 5: Refactored to delegate module-specific logic to ModuleRegistry.
 * The class is now a thin orchestrator:
 *  - auth & scope checks
 *  - dispatch to module handlers via ModuleRegistry
 *  - delegation to legacy SubmissionSnapshotService/SubmissionWorkflowService
 *  - generation of submission number, package signature, etc.
 *
 * Module definitions (which entity types, which period semantics) live in
 * app/Services/Submission/Modules. Add a new module = create handler + register.
 */
class SubmissionBatchService
{
    public const MODULE_RUMAH = SubmissionBatch::MODULE_RUMAH;
    public const MODULE_KELUARGA = SubmissionBatch::MODULE_KELUARGA;
    public const MODULE_PENDUDUK = SubmissionBatch::MODULE_PENDUDUK;
    public const MODULE_KESEHATAN = SubmissionBatch::MODULE_KESEHATAN;
    public const MODULE_PENDIDIKAN = SubmissionBatch::MODULE_PENDIDIKAN;
    public const MODULE_PUSKES = SubmissionBatch::MODULE_PUSKES;
    public const MODULE_SEKOLAH = SubmissionBatch::MODULE_SEKOLAH;
    public const MODULE_LAPANGAN = SubmissionBatch::MODULE_LAPANGAN;

    private readonly ModuleRegistry $registry;

    public function __construct(
        private readonly SubmissionSnapshotService $snapshotService,
        private readonly SubmissionWorkflowService $workflowService,
        ModuleRegistry $registry,
    ) {
        $this->registry = $registry->has(self::MODULE_RUMAH) ? $registry : ModuleRegistry::default();
    }

    public function moduleDefinitions(): array
    {
        return $this->registry->definitions();
    }

    public function preview(int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null, ?User $actor = null): array
    {
        $periodeBulan ??= now()->month;
        $semester ??= $this->defaultSemester();
        $periodeTahun ??= now()->year;

        return [
            'periode_bulan' => $periodeBulan,
            'semester' => $semester,
            'periode_tahun' => $periodeTahun,
            'modules' => collect($this->registry->definitions())
                ->when($actor && $this->isSupportedOpdSubmitter($actor), fn ($definitions) => $definitions->filter(fn (array $_, string $module): bool => in_array($module, $this->modulesForOpdActor($actor), true)))
                ->map(fn (array $definition, string $module): array => $this->modulePreviewRow($module, $kecamatanId, $periodeBulan, $semester, $periodeTahun, $definition, $actor))
                ->values()
                ->all(),
        ];
    }

    public function submit(int $kecamatanId, array $modules = [], ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null, ?string $notes = null, ?User $actor = null): Collection
    {
        $actor ??= auth()->user();

        if (! $actor) {
            throw new RuntimeException('Pengguna harus terautentikasi untuk mengirim batch.');
        }

        $isOpdSubmitter = $this->isSupportedOpdSubmitter($actor);
        if ($isOpdSubmitter) {
            $modules = array_values(array_intersect($this->normalizeModules($modules), $this->modulesForOpdActor($actor)));
            if ($modules === []) {
                throw new AuthorizationException('OPD hanya dapat mengirim dataset sektoral sesuai kewenangannya.');
            }
            if ($actor->kecamatan_id && (int) $actor->kecamatan_id !== $kecamatanId) {
                throw new AuthorizationException('Akun OPD tidak dapat mengirim laporan kecamatan lain.');
            }
        }

        if ($actor->hasRole('admin_desa')) {
            $modules = array_values(array_intersect(
                $this->normalizeModules($modules),
                [self::MODULE_RUMAH, self::MODULE_KELUARGA, self::MODULE_PENDUDUK],
            ));
        }

        $desaId = $this->assertActorScope($actor, $kecamatanId, $modules);

        $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->parent_id;
        if ($actor->hasAnyRole(['admin_kecamatan', 'admin_desa']) && (int) $actorKecamatanId !== $kecamatanId) {
            throw new AuthorizationException('Admin Kecamatan tidak dapat mengirim data kecamatan lain.');
        }

        if (! $actor->hasAnyRole(['admin_kecamatan', 'admin_desa', 'admin', 'super_admin', 'admin_kominfo']) && ! $isOpdSubmitter) {
            throw new AuthorizationException('Role ini tidak memiliki izin membuat batch pengiriman.');
        }

        return DB::transaction(function () use ($kecamatanId, $modules, $periodeBulan, $semester, $periodeTahun, $notes, $actor, $desaId, $isOpdSubmitter): Collection {
            Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->findOrFail($kecamatanId);
            $selectedModules = $this->normalizeModules($modules);

            if ($selectedModules === []) {
                throw new RuntimeException('Pilih minimal satu modul data untuk dikirim.');
            }

            $modulePayloads = collect($selectedModules)
                ->map(function (string $module) use ($kecamatanId, $periodeBulan, $semester, $periodeTahun, $desaId, $actor, $isOpdSubmitter): array {
                    $handler = $this->registry->get($module);
                    $context = $handler->context($periodeBulan, $semester, $periodeTahun);
                    $query = $handler->query($kecamatanId, $context, $desaId);
                    if ($isOpdSubmitter) {
                        $query->where('created_by', $actor->id);
                    }
                    $records = $query->get();
                    $ids = $records->pluck('id')->values();

                    return [
                        'module' => $module,
                        'context' => $context,
                        'ids' => $ids,
                        'records' => $records,
                        'count' => $ids->count(),
                    ];
                })
                ->filter(fn (array $payload): bool => $payload['count'] > 0)
                ->values();

            if ($modulePayloads->isEmpty()) {
                throw new RuntimeException('Tidak ada data untuk dikirim pada modul yang dipilih.');
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
                    throw new RuntimeException('Sudah ada batch aktif untuk modul '.$this->moduleLabel($payload['module']).' pada kecamatan dan periode yang sama.');
                }
            });

            $modulesWithData = $modulePayloads->pluck('module')->all();
            $batchModule = count($modulesWithData) === 1 ? $modulesWithData[0] : SubmissionBatch::MODULE_MIXED;
            $primaryContext = $this->batchContext($modulePayloads);
            $previousVersions = $this->previousSubmissionVersions($kecamatanId, $modulesWithData, $modulePayloads);
            $previousVersion = $previousVersions->sortByDesc('id')->first();
            $snapshotVersion = $previousVersions->count() + 1;

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
            $batch->payload_snapshot = $this->withRevisionMetadata(
                $batch->payload_snapshot,
                $snapshotVersion,
                $previousVersion,
            );

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

    /**
     * Submit a batch from mobile sync with pre-created entity IDs.
     *
     * P1-A FIX: Mobile sync now goes through the same service layer as the web panel,
     * ensuring consistent batch creation, validation, and workflow transitions.
     *
     * BEFORE (broken):
     *   - MobileSyncController created SubmissionBatch directly, bypassing service
     *   - submitted_at set while status=DRAFT
     *   - No active batch conflict check
     *   - Manual snapshot building
     *   - No revision metadata
     *
     * AFTER (fixed):
     *   - Goes through service layer (auth, validation, conflict check)
     *   - submitted_at set when status becomes PENDING (via workflow)
     *   - activeBatchForModule() conflict check applied
     *   - Proper payload snapshot with revision metadata
     *   - Consistent workflow transition
     *
     * @param  int      $kecamatanId
     * @param  array    $entityPayloads  [['module' => 'rumah', 'ids' => [1,2], 'records' => Collection], ...]
     * @param  string|null $notes
     * @param  User|null  $actor
     * @return Collection  Collection containing the submitted SubmissionBatch
     * @throws RuntimeException When no entities provided
     * @throws RuntimeException When active batch already exists
     * @throws AuthorizationException      When actor not authorized
     */
    public function mobileSyncPush(int $kecamatanId, array $entityPayloads, ?string $notes = null, ?User $actor = null): Collection
    {
        $actor ??= auth()->user();

        if (! $actor) {
            throw new RuntimeException('Pengguna harus terautentikasi untuk mengirim batch.');
        }

        if (! $actor->hasRole('admin_desa')) {
            throw new AuthorizationException('Hanya Admin Desa yang dapat mengirim data via mobile sync.');
        }

        $desaId = $actor->desa_id;
        $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->parent_id;
        if ((int) $actorKecamatanId !== $kecamatanId) {
            throw new AuthorizationException('Admin Desa tidak dapat mengirim data kecamatan lain.');
        }

        if (! $desaId) {
            throw new RuntimeException('Akun Admin Desa belum terhubung ke desa.');
        }

        if ($entityPayloads === []) {
            throw new RuntimeException('Tidak ada data untuk dikirim.');
        }

        return DB::transaction(function () use ($kecamatanId, $entityPayloads, $notes, $actor, $desaId): Collection {
            // Flat associative array from mobile controller: ['rumah' => ['ids' => [...], 'records' => ...], ...]
            // Convert to indexed format for consistent internal processing
            $flatPayloads = collect($entityPayloads)
                ->map(function (array $payload, string $module) use ($actor): ?array {
                    $ids = collect($payload['ids']);
                    $records = $payload['records'] ?? collect();

                    // Validate that the IDs actually exist and belong to this desa
                    $validIds = $this->validateMobileEntityIds($module, $ids->all(), $actor);

                    if ($validIds->isEmpty()) {
                        return null;
                    }

                    $handler = $this->registry->has($module)
                        ? $this->registry->get($module)
                        : null;

                    $context = [
                        'periode_bulan' => null,
                        'semester' => null,
                        'periode_tahun' => null,
                    ];

                    return [
                        'module' => $module,
                        'context' => $context,
                        'ids' => $validIds,
                        'records' => $records->filter(fn ($r) => $validIds->contains($r->getKey())),
                        'count' => $validIds->count(),
                    ];
                })
                ->filter()
                ->values();

            if ($flatPayloads->isEmpty()) {
                throw new RuntimeException('Tidak ada data valid untuk dikirim.');
            }

            // Check for active batch conflicts
            $modulesWithData = $flatPayloads->pluck('module')->all();
            $this->checkMobileBatchConflicts($kecamatanId, $modulesWithData, $flatPayloads);

            $batchModule = count($modulesWithData) === 1 ? $modulesWithData[0] : SubmissionBatch::MODULE_MIXED;
            $primaryContext = $this->batchContext($flatPayloads);
            $previousVersions = $this->previousSubmissionVersions($kecamatanId, $modulesWithData, $flatPayloads);
            $previousVersion = $previousVersions->sortByDesc('id')->first();
            $snapshotVersion = $previousVersions->count() + 1;

            // Create batch
            $batch = SubmissionBatch::create([
                'module' => $batchModule,
                'submission_number' => $this->generateMobileSubmissionNumber($batchModule, $kecamatanId, $actor),
                'kecamatan_id' => $kecamatanId,
                'periode_bulan' => $primaryContext['periode_bulan'],
                'semester' => $primaryContext['semester'],
                'periode_tahun' => $primaryContext['periode_tahun'],
                'package_signature' => $this->packageSignature($modulesWithData),
                'package_keys' => $modulesWithData,
                'package_summary' => $this->modulePackageSummary($flatPayloads),
                'status' => SubmissionBatch::STATUS_DRAFT,
                'notes' => $notes,
                'created_by' => $actor->id,
            ]);

            // Build snapshot
            $moduleSnapshots = $this->moduleSnapshots($flatPayloads, $actor);
            $batch->payload_snapshot = count($modulesWithData) === 1
                ? $moduleSnapshots[0]
                : $this->snapshotService->buildMixedSnapshot($batch, $moduleSnapshots, $actor);
            $batch->payload_snapshot = $this->withRevisionMetadata(
                $batch->payload_snapshot,
                $snapshotVersion,
                $previousVersion,
            );
            // Mark as mobile source
            $batch->payload_snapshot['source'] = 'mobile';

            $batch->version = 1;
            $batch->saveQuietly();

            // Create items linking entities to batch
            $this->createMixedItems($batch, $flatPayloads);

            // Log audit trail
            $this->snapshotService->logBatchSnapshot($batch, $actor);

            // Transition to PENDING via workflow service
            return collect([$this->workflowService->submit($batch, $actor, $notes)]);
        });
    }

    /**
     * Validate that entity IDs exist and belong to the actor's desa.
     * For mobile, we only accept rumah/keluarga/penduduk entities created by the actor.
     */
    private function validateMobileEntityIds(string $module, array $ids, User $actor): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $validIds = collect();

        foreach ($ids as $id) {
            $valid = match ($module) {
                self::MODULE_RUMAH => Rumah::query()
                    ->whereKey($id)
                    ->where('desa_id', $actor->desa_id)
                    ->exists(),
                self::MODULE_KELUARGA => Keluarga::query()
                    ->whereKey($id)
                    ->whereHas('rumah', fn ($q) => $q->where('desa_id', $actor->desa_id))
                    ->exists(),
                self::MODULE_PENDUDUK => Penduduk::query()
                    ->whereKey($id)
                    ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $actor->desa_id))
                    ->exists(),
                default => false,
            };

            if ($valid) {
                $validId = (int) $id;
                $validIds->push($validId);
            }
        }

        return $validIds;
    }

    /**
     * Check for active batch conflicts before mobile sync submission.
     */
    private function checkMobileBatchConflicts(int $kecamatanId, array $modules, Collection $modulePayloads): void
    {
        $modulePayloads->each(function (array $payload) use ($kecamatanId): void {
            $existingBatch = $this->activeBatchForModule(
                $kecamatanId,
                $payload['module'],
                $payload['context']['periode_bulan'],
                $payload['context']['semester'],
                $payload['context']['periode_tahun'],
            );

            if ($existingBatch) {
                throw new RuntimeException(
                    'Sudah ada batch aktif untuk modul '.$this->moduleLabel($payload['module'])
                    .'. Selesaikan atau batalkan batch sebelumnya sebelum mengirim data baru.'
                );
            }
        });
    }

    /**
     * Generate submission number for mobile batches.
     */
    private function generateMobileSubmissionNumber(string $module, int $kecamatanId, User $actor): string
    {
        $prefix = sprintf(
            'SB-M-%s-%d-%s',
            strtoupper($module),
            $kecamatanId,
            $actor->id,
        );
        $hash = substr(md5($actor->email.now()->timestamp), 0, 6);
        $timestamp = now()->format('ymdHis');

        $sequence = SubmissionBatch::withTrashed()
            ->where('submission_number', 'like', "{$prefix}%")
            ->count() + 1;

        return sprintf('%s-%s-%03d', $prefix, $hash, $sequence);
    }

    public function registry(): ModuleRegistry
    {
        return $this->registry;
    }

    // ===========================
    // Internal helpers
    // ===========================

    private function modulePreviewRow(string $module, int $kecamatanId, int $periodeBulan, string $semester, int $periodeTahun, array $definition, ?User $actor = null): array
    {
        $handler = $definition['handler'] ?? $this->registry->get($module);
        $context = $handler->context($periodeBulan, $semester, $periodeTahun);
        $desaId = $actor ? $this->assertActorScope($actor, $kecamatanId, [$module]) : null;
        $query = $handler->query($kecamatanId, $context, $desaId);
        if ($actor && $this->isSupportedOpdSubmitter($actor) && in_array($module, $this->modulesForOpdActor($actor), true)) {
            $query->where('created_by', $actor->id);
        }
        $count = $query->count();
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

    private function assertActorScope(User $actor, int $kecamatanId, array $modules): ?int
    {
        $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->parent_id;
        if ($actor->hasAnyRole(['admin_kecamatan', 'admin_desa']) && (int) $actorKecamatanId !== $kecamatanId) {
            throw new AuthorizationException('Akun tidak dapat mengirim data kecamatan lain.');
        }

        if ($actor->hasRole('admin_desa')) {
            if (! $actor->desa_id || (int) $actor->desa?->parent_id !== $kecamatanId) {
                throw new AuthorizationException('Akun Desa belum memiliki wilayah yang valid.');
            }

            return (int) $actor->desa_id;
        }

        return null;
    }

    private function isSupportedOpdSubmitter(User $actor): bool
    {
        return $actor->hasAnyRole(['admin_dinkes', 'operator_dinkes', 'admin_dispend', 'operator_dispend']);
    }

    private function modulesForOpdActor(User $actor): array
    {
        if ($actor->hasAnyRole(['admin_dinkes', 'operator_dinkes'])) {
            return [self::MODULE_KESEHATAN];
        }

        if ($actor->hasAnyRole(['admin_dispend', 'operator_dispend'])) {
            return [self::MODULE_PENDIDIKAN];
        }

        return [];
    }

    private function moduleLabel(string $module): string
    {
        return $this->registry->has($module) ? $this->registry->get($module)->label() : SubmissionBatch::moduleLabel($module);
    }

    private function generateSubmissionNumber(string $module, int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null): string
    {
        $periodKey = match ($module) {
            self::MODULE_KESEHATAN => sprintf('B%02d-T%d', $periodeBulan ?? now()->month, $periodeTahun ?? now()->year),
            self::MODULE_PENDIDIKAN => sprintf('%s-T%d', strtoupper($semester ?? $this->defaultSemester()), $periodeTahun ?? now()->year),
            self::MODULE_PUSKES => sprintf('PKS-B%02d-T%d', $periodeBulan ?? now()->month, $periodeTahun ?? now()->year),
            self::MODULE_SEKOLAH => sprintf('SKL-%s-T%d', strtoupper($semester ?? $this->defaultSemester()), $periodeTahun ?? now()->year),
            SubmissionBatch::MODULE_MIXED => sprintf('MIXED-T%d', $periodeTahun ?? now()->year),
            default => 'MASTER',
        };

        $prefix = sprintf('SB-%s-%s-%s', strtoupper($module), $kecamatanId, $periodKey);
        $sequence = SubmissionBatch::withTrashed()
            ->where('submission_number', 'like', "{$prefix}%")
            ->count() + 1;

        return sprintf('%s-%03d', $prefix, $sequence);
    }

    private function createMixedItems(SubmissionBatch $batch, Collection $modulePayloads): void
    {
        $rows = [];
        $opdBatchAssignments = [];
        $childTableAssignments = [];

        foreach ($modulePayloads as $payload) {
            $handler = $this->registry->get($payload['module']);
            $entityType = $handler->entityType();

            if (! $entityType) {
                continue;
            }

            $this->appendEntityRows($rows, $batch->id, $payload['ids'], $entityType, $payload['module']);

            if (in_array($payload['module'], [self::MODULE_KESEHATAN, self::MODULE_PENDIDIKAN, self::MODULE_PUSKES, self::MODULE_SEKOLAH], true)) {
                $opdBatchAssignments[] = [
                    'model' => $entityType,
                    'ids' => $payload['ids'],
                    'batch_id' => $batch->id,
                ];
            }

            // puskes module: assign child records (stok_obats, rekam_medis)
            if ($payload['module'] === self::MODULE_PUSKES) {
                $childTableAssignments[] = [
                    'model' => StokObat::class,
                    'foreign_key' => 'fasilitas_kesehatan_id',
                    'parent_ids' => $payload['ids'],
                    'batch_id' => $batch->id,
                ];
                $childTableAssignments[] = [
                    'model' => RekamMedis::class,
                    'foreign_key' => 'fasilitas_kesehatan_id',
                    'parent_ids' => $payload['ids'],
                    'batch_id' => $batch->id,
                ];
            }

            // sekolah module: assign child records (data_gurus, data_siswas, data_prasaranas)
            if ($payload['module'] === self::MODULE_SEKOLAH) {
                $childTableAssignments[] = [
                    'model' => DataGuru::class,
                    'foreign_key' => 'sekolah_id',
                    'parent_ids' => $payload['ids'],
                    'batch_id' => $batch->id,
                ];
                $childTableAssignments[] = [
                    'model' => DataSiswa::class,
                    'foreign_key' => 'sekolah_id',
                    'parent_ids' => $payload['ids'],
                    'batch_id' => $batch->id,
                ];
                $childTableAssignments[] = [
                    'model' => DataPrasarana::class,
                    'foreign_key' => 'sekolah_id',
                    'parent_ids' => $payload['ids'],
                    'batch_id' => $batch->id,
                ];
            }
        }

        if ($rows !== []) {
            SubmissionBatchItem::query()->insert($rows);
        }

        // Assign submission_batch_id to OPD parent tables
        foreach ($opdBatchAssignments as $assignment) {
            $assignment['model']::query()
                ->whereKey($assignment['ids'])
                ->update(['submission_batch_id' => $assignment['batch_id']]);
        }

        // Assign submission_batch_id to child tables
        foreach ($childTableAssignments as $assignment) {
            $assignment['model']::query()
                ->whereIn($assignment['foreign_key'], $assignment['parent_ids'])
                ->update(['submission_batch_id' => $assignment['batch_id']]);
        }
    }

    private function moduleSnapshots(Collection $modulePayloads, User $actor): array
    {
        return $modulePayloads
            ->map(function (array $payload) use ($actor): array {
                $handler = $this->registry->get($payload['module']);

                return [
                    'module' => $payload['module'],
                    'module_label' => $handler->label(),
                    'periode_bulan' => $payload['context']['periode_bulan'],
                    'semester' => $payload['context']['semester'],
                    'periode_tahun' => $payload['context']['periode_tahun'],
                    'count' => $payload['count'],
                    'entity_ids' => $payload['ids']->all(),
                    'records' => $handler->snapshotRows($payload['records'], $payload['context']),
                    'created_by' => $actor->id,
                    'created_at' => now()->toDateTimeString(),
                ];
            })
            ->values()
            ->all();
    }

    private function modulePackageSummary(Collection $modulePayloads): array
    {
        return $modulePayloads
            ->map(fn (array $payload): array => [
                'key' => $payload['module'],
                'label' => $this->registry->get($payload['module'])->label(),
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

    private function previousSubmissionVersions(int $kecamatanId, array $modules, Collection $modulePayloads): Collection
    {
        $signature = $this->packageSignature($modules);

        return SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->where('package_signature', $signature)
            ->with('items')
            ->get()
            ->filter(fn (SubmissionBatch $batch): bool => $modulePayloads->every(
                fn (array $payload): bool => $this->batchContainsModule($batch, $payload['module'])
                    && $this->batchMatchesModulePeriod(
                        $batch,
                        $payload['module'],
                        $payload['context']['periode_bulan'],
                        $payload['context']['semester'],
                        $payload['context']['periode_tahun'],
                    )
            ))
            ->values();
    }

    private function withRevisionMetadata(array $snapshot, int $snapshotVersion, ?SubmissionBatch $previousVersion): array
    {
        $snapshot['snapshot_version'] = $snapshotVersion;
        $snapshot['revision'] = [
            'number' => $snapshotVersion,
            'previous_submission_batch_id' => $previousVersion?->id,
            'previous_submission_number' => $previousVersion?->submission_number,
            'previous_status' => $previousVersion?->status,
            'created_at' => now()->toDateTimeString(),
        ];

        return $snapshot;
    }

    private function activeBatchForModule(int $kecamatanId, string $module, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): ?SubmissionBatch
    {
        $query = SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->whereIn('status', [
                SubmissionBatch::STATUS_DRAFT,
                SubmissionBatch::STATUS_PENDING,
                SubmissionBatch::STATUS_IN_REVIEW,
            ])
            ->with('items');

        $query->where(function ($q) use ($module): void {
            $q->where('module', $module);
            $q->orWhereRaw('JSON_CONTAINS(package_keys, ?)', [json_encode($module)]);
        });

        if ($module === self::MODULE_KESEHATAN && $periodeBulan !== null && $periodeTahun !== null) {
            $query->where('periode_bulan', $periodeBulan)
                  ->where('periode_tahun', $periodeTahun);
        }
        if ($module === self::MODULE_PENDIDIKAN && $semester !== null && $periodeTahun !== null) {
            $query->where('semester', $semester)
                  ->where('periode_tahun', $periodeTahun);
        }
        if ($module === self::MODULE_PUSKES && $periodeBulan !== null && $periodeTahun !== null) {
            $query->where('periode_bulan', $periodeBulan)
                  ->where('periode_tahun', $periodeTahun);
        }
        if ($module === self::MODULE_SEKOLAH && $semester !== null && $periodeTahun !== null) {
            $query->where('semester', $semester)
                  ->where('periode_tahun', $periodeTahun);
        }

        $query->lockForUpdate();

        $batch = $query->first();

        if (! $batch) {
            return null;
        }

        if (! $this->batchMatchesModulePeriod($batch, $module, $periodeBulan, $semester, $periodeTahun)) {
            return null;
        }

        return $batch;
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
        $available = $this->registry->availableKeys();
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
        $available = $this->registry->availableKeys();

        return collect($modules)
            ->map(fn ($module) => is_string($module) ? strtolower(trim($module)) : null)
            ->filter()
            ->unique()
            ->intersect($available)
            ->sort()
            ->values()
            ->all();
    }

    private function packageSignature(array $modules): string
    {
        return implode('+', $this->normalizeModules($modules));
    }

    private function defaultSemester(): string
    {
        return now()->month <= 6 ? 'ganjil' : 'genap';
    }
}
