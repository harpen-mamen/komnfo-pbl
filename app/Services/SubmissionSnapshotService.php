<?php

namespace App\Services;

use RuntimeException;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Rumah;
use App\Models\Keluarga;
use App\Models\Penduduk;
use Throwable;
use App\Models\AuditTrail;
use App\Models\SubmissionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * SubmissionSnapshotService — Payload snapshot building & integrity.
 *
 * SERVICE LAYER: Arsitektur §7.1
 * Handles building, serializing, and validating batch payload snapshots.
 *
 * Responsibilities:
 * - Build snapshot for a single module
 * - Build snapshot for mixed modules
 * - Serialize entity records with appropriate fields
 * - Calculate snapshot integrity hash
 * - Validate snapshot completeness before publish
 */
class SubmissionSnapshotService
{
    /**
     * Fields to include in snapshot for each entity type.
     * Excludes internal fields like id, created_by, timestamps.
     */
    private const SNAPSHOT_FIELDS = [
        'Rumah' => [
            'kode_rumah', 'alamat_lengkap', 'latitude', 'longitude',
            'status_rumah', 'keterangan', 'publik', 'desa_id',
        ],
        'Keluarga' => [
            'rumah_id', 'nomor_kk', 'nama_kepala_keluarga',
            'jumlah_anggota', 'status_ekonomi',
        ],
        'Penduduk' => [
            'keluarga_id', 'nik', 'nama_lengkap', 'jenis_kelamin',
            'tempat_lahir', 'tanggal_lahir', 'agama', 'status_perkawinan',
            'pendidikan', 'pekerjaan', 'penghasilan', 'status_dalam_keluarga',
            'nomor_hp', 'status_penduduk',
        ],
        // DataKesehatanBulanan and DataPendidikanBulanan tables removed
    ];

    public function assertCanSubmitToKecamatan(int $kecamatanId, ?User $actor): void
    {
        if (! $actor) {
            throw new RuntimeException('Pengguna harus terautentikasi untuk mengirim batch.');
        }

        if ($actor->hasRole('admin_kecamatan') && (int) $actor->kecamatan_id !== $kecamatanId) {
            throw new AuthorizationException('Admin Kecamatan tidak dapat mengirim data kecamatan lain.');
        }
    }

    /**
     * Build period context for a module.
     * ARUSITEKTUR §2.3: Period semantics per module type.
     */
    public function buildContext(string $module, ?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        $periodeTahun ??= now()->year;

        return match ($module) {
            SubmissionBatchService::MODULE_KESEHATAN => [
                'periode_bulan' => $periodeBulan ?? now()->month,
                'semester' => null,
                'periode_tahun' => $periodeTahun,
            ],
            SubmissionBatchService::MODULE_PENDIDIKAN => [
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

    public function defaultSemester(): string
    {
        return now()->month <= 6 ? 'ganjil' : 'genap';
    }

    /**
     * Build a snapshot for a single module.
     * READ MODEL PREP: Snapshot fields are filtered per entity type.
     */
    public function buildSnapshot(
        SubmissionBatch $batch,
        string $module,
        array $context,
        int $count,
        User $actor
    ): array {
        return [
            'module' => $module,
            'module_label' => SubmissionBatch::moduleLabel($module),
            'periode_bulan' => $context['periode_bulan'],
            'semester' => $context['semester'],
            'periode_tahun' => $context['periode_tahun'],
            'count' => $count,
            'created_by' => $actor->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Build a snapshot for mixed (multi-module) batch.
     */
    public function buildMixedSnapshot(
        SubmissionBatch $batch,
        array $moduleSnapshots,
        User $actor
    ): array {
        return [
            'module' => $batch->module,
            'module_label' => SubmissionBatch::moduleLabel($batch->module),
            'modules' => $moduleSnapshots,
            'total_count' => collect($moduleSnapshots)->sum('count'),
            'created_by' => $actor->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Serialize a collection of model records for snapshot.
     * Only includes defined snapshot fields per entity type.
     * Calculates integrity hash.
     *
     * @param  Collection|\Illuminate\Database\Eloquent\Collection  $records
     * @param  array  $context  Periode context
     * @return array
     */
    public function serializeRecords(Collection $records, string $entityType, array $context = []): array
    {
        $modelName = class_basename($entityType);
        $allowedFields = self::SNAPSHOT_FIELDS[$modelName] ?? null;

        return $records->map(function (Model $record) use ($allowedFields, $context): array {
            $data = $allowedFields
                ? collect($record->getAttributes())->only($allowedFields)->toArray()
                : $record->getAttributes();

            return [
                'entity_id' => $record->getKey(),
                'attributes' => $data,
                'snapshot_at' => now()->toDateTimeString(),
                'context' => $context,
            ];
        })->all();
    }

    /**
     * Calculate SHA-256 integrity hash for a snapshot.
     * Used to detect duplicate or tampered submissions.
     */
    public function calculateIntegrityHash(array $snapshot): string
    {
        $payload = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $payload);
    }

    /**
     * Verify snapshot integrity before publishing.
     * Checks that entity IDs still exist and counts match.
     *
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public function validateSnapshotIntegrity(SubmissionBatch $batch): array
    {
        $errors = [];
        $snapshot = $batch->payload_snapshot ?? [];

        if (empty($snapshot)) {
            return ['valid' => false, 'errors' => ['Snapshot is empty']];
        }

        $modules = $snapshot['modules'] ?? [$snapshot];

        foreach ($modules as $moduleSnapshot) {
            $module = $moduleSnapshot['module'] ?? 'unknown';
            $entityIds = $moduleSnapshot['entity_ids'] ?? [];
            $records = $moduleSnapshot['records'] ?? [];
            $entityType = $this->entityTypeForModule($module);

            if (! $entityType) {
                $errors[] = "Module [{$module}]: unknown entity type";
                continue;
            }

            // Verify entity IDs exist
            $model = new $entityType();
            $existingCount = $model->query()->whereKey($entityIds)->count();
            $expectedCount = count($entityIds);

            if ($existingCount !== $expectedCount) {
                $errors[] = "Module [{$module}]: entity count mismatch (expected {$expectedCount}, found {$existingCount})";
            }

            // Verify records match entity IDs
            if (is_array($records) && count($records) !== $expectedCount) {
                $errors[] = "Module [{$module}]: record count mismatch (expected {$expectedCount}, got " . count($records) . ")";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'batch_id' => $batch->id,
            'validated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Map module key to fully-qualified entity class name.
     */
    public function entityTypeForModule(string $module): ?string
    {
        return match ($module) {
            SubmissionBatchService::MODULE_RUMAH => Rumah::class,
            SubmissionBatchService::MODULE_KELUARGA => Keluarga::class,
            SubmissionBatchService::MODULE_PENDUDUK => Penduduk::class,
            // DataKesehatanBulanan and DataPendidikanBulanan tables removed
            SubmissionBatchService::MODULE_KESEHATAN => null,
            SubmissionBatchService::MODULE_PENDIDIKAN => null,
            default => null,
        };
    }

    public function logBatchSnapshot(SubmissionBatch $batch, User $actor): void
    {
        try {
            AuditTrail::query()->create([
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
        } catch (Throwable $e) {
            Log::warning('Failed to write submission snapshot audit', ['error' => $e->getMessage()]);
        }
    }
}
