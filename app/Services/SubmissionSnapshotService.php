<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Log;

class SubmissionSnapshotService
{
    public function assertCanSubmitToKecamatan(int $kecamatanId, ?User $actor): void
    {
        if (! $actor) {
            throw new \RuntimeException('Pengguna harus terautentikasi untuk mengirim batch.');
        }

        if ($actor->hasRole('admin_kecamatan') && (int) $actor->kecamatan_id !== $kecamatanId) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Admin Kecamatan tidak dapat mengirim data kecamatan lain.');
        }
    }

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
        } catch (\Throwable $e) {
            Log::warning('Failed to write submission snapshot audit', ['error' => $e->getMessage()]);
        }
    }
}

