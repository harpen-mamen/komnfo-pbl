<?php

namespace App\Http\Controllers\Api;

use App\Models\Keluarga;
use App\Models\MobileSyncOperation;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Services\BatchApprovalService;
use App\Services\SubmissionBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class MobileSyncController
{
    /**
     * Push mobile draft operations to the server.
     *
     * P1-A FIX: All mobile data now goes through SubmissionBatchService::mobileSyncPush(),
     * ensuring consistent batch creation, validation, and workflow transitions
     * with the web panel.
     *
     * FLOW:
     *  1. Validate operations (idempotent check)
     *  2. Create entities (rumah/keluarga/penduduk)
     *  3. Call mobileSyncPush() which:
     *     - Validates active batch conflicts
     *     - Creates SubmissionBatch with proper metadata
     *     - Links entities via SubmissionBatchItem
     *     - Creates payload snapshot with revision metadata
     *     - Transitions to PENDING via workflow service
     *  4. Record MobileSyncOperation for each entity
     *
     * BEFORE FIX:
     *   - SubmissionBatch created directly in controller (bypassed service)
     *   - submitted_at set while status=DRAFT
     *   - No duplicate batch validation
     *   - Manual snapshot building
     *   - No revision metadata
     *
     * AFTER FIX:
     *   - Full service layer integration
     *   - Consistent with web panel workflow
     *   - All validation, versioning, snapshot applied correctly
     */
    public function push(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, "SIPDT Mobile hanya tersedia untuk Admin Desa.");

        $data = $request->validate([
            "device_id" => ["required", "string", "max:120"],
            "operations" => ["required", "array", "max:100"],
            "operations.*.operation_id" => ["required", "uuid"],
            "operations.*.dataset" => ["required", "string", "max:80", "in:rumah,keluarga,penduduk"],
            "operations.*.local_id" => ["required", "string", "max:120"],
            "operations.*.base_version" => ["nullable", "integer", "min:0"],
            "operations.*.payload" => ["required", "array"],
        ]);

        // Maps local_id to server_id for cross-operation references
        $localIdToServerId = [];
        // Group entity IDs by module for batch submission
        $entityPayloads = [];
        $operationResults = collect();

        // Pass 1: Process each operation
        // - Check idempotent (same operation_id)
        // - Create entity
        // - Record MobileSyncOperation
        foreach ($data["operations"] as $operation) {
            $result = $this->processMobileOperation(
                $request,
                $data["device_id"],
                $operation,
                $localIdToServerId,
                $entityPayloads,
            );
            $operationResults->push($result);
        }

        // If no successful operations, return early
        if ($operationResults->every(fn ($r) => $r["status"] === "failed")) {
            return $this->buildResponse($operationResults, null, null);
        }

        // Pass 2: Submit batch via service layer
        // This ensures full validation, conflict check, snapshot, versioning, workflow
        $submittedBatch = null;
        $kecamatanId = $request->user()->kecamatan_id ?? $request->user()->desa?->kecamatan_id;

        if ($kecamatanId !== null && !empty($entityPayloads)) {
            try {
                $batches = app(SubmissionBatchService::class)->mobileSyncPush(
                    $kecamatanId,
                    $entityPayloads,
                    "Pengiriman data dari aplikasi mobile oleh Admin Desa.",
                    $request->user(),
                );

                if ($batches->isNotEmpty()) {
                    $submittedBatch = $batches->first();

                    // Update MobileSyncOperation with batch_id and status
                    MobileSyncOperation::query()
                        ->where("user_id", $request->user()->id)
                        ->whereIn("operation_id", collect($data["operations"])->pluck("operation_id"))
                        ->update([
                            "batch_id" => $submittedBatch->id,
                            "status" => "pending",
                        ]);

                    // Refresh operation results with batch status
                    $operationResults = $operationResults->map(function (array $result) use ($submittedBatch): array {
                        $result["batch_id"] = $submittedBatch->id;
                        $result["batch_status"] = $submittedBatch->status;
                        $result["batch_status_label"] = SubmissionBatch::statusLabel($submittedBatch->status);
                        return $result;
                    });
                }
            } catch (\Throwable $e) {
                // If batch submission fails, rollback by marking operations as failed
                MobileSyncOperation::query()
                    ->where("user_id", $request->user()->id)
                    ->whereIn("operation_id", collect($data["operations"])->pluck("operation_id"))
                    ->where("status", "pending")
                    ->update([
                        "status" => "failed",
                        "error_message" => $e->getMessage(),
                    ]);

                // Mark successful operations back to failed
                $operationResults = $operationResults->map(function (array $result) use ($e): array {
                    if ($result["status"] === "accepted") {
                        return $this->buildResult(
                            ["operation_id" => $result["operation_id"], "local_id" => $result["local_id"]],
                            "failed",
                            null,
                            $e->getMessage(),
                            null,
                        );
                    }
                    return $result;
                });

                return $this->buildResponse($operationResults, null, null, $e->getMessage());
            }
        }

        return $this->buildResponse($operationResults, $submittedBatch, null);
    }

    /**
     * Process a single mobile operation.
     *
     * Creates the entity, tracks local-to-server ID mapping, records MobileSyncOperation,
     * and groups entity IDs by module for batch submission.
     */
    private function processMobileOperation(
        Request $request,
        string $deviceId,
        array $operation,
        array &$localIdToServerId,
        array &$entityPayloads,
    ): array {
        try {
            // Check idempotent
            $existing = MobileSyncOperation::query()
                ->where("user_id", $request->user()->id)
                ->where("operation_id", $operation["operation_id"])
                ->first();

                if ($existing) {
                    $result = $this->buildResult($operation, $existing->status, $existing->server_id, null, $existing->batch_id);

                    // Enhance with batch status if batch exists
                    if ($existing->batch_id) {
                        $batch = SubmissionBatch::query()->find($existing->batch_id);
                        if ($batch) {
                            $result["batch_status"] = $batch->status;
                            $result["batch_status_label"] = SubmissionBatch::statusLabel($batch->status);
                        }
                    }

                    return $result;
                }

                // Create entity
                $module = $operation["dataset"];
                $serverId = match ($module) {
                    "rumah" => $this->storeRumah($request, $operation["payload"]),
                    "keluarga" => $this->storeKeluarga($request, $operation["payload"], $localIdToServerId),
                    "penduduk" => $this->storePenduduk($request, $operation["payload"], $localIdToServerId),
                };

                // Track local-to-server mapping
                $localIdToServerId[$operation["local_id"]] = $serverId;

                // Group entity IDs by module for batch submission
                $this->groupEntityPayload($module, $serverId, $entityPayloads);

                // Record mobile sync operation
                MobileSyncOperation::query()->create([
                    "operation_id" => $operation["operation_id"],
                    "user_id" => $request->user()->id,
                    "device_id" => $deviceId,
                    "dataset" => $operation["dataset"],
                    "local_id" => $operation["local_id"],
                    "base_version" => $operation["base_version"] ?? 0,
                    "payload" => $operation["payload"],
                    "status" => "accepted",
                    "server_id" => $serverId,
                    "processed_at" => Carbon::now(),
                ]);

                return $this->buildResult($operation, "accepted", $serverId, null, null);
        } catch (ValidationException $exception) {
            return $this->buildResult(
                $operation,
                "failed",
                null,
                collect($exception->errors())->flatten()->first() ?? "Data tidak valid.",
            );
        } catch (Throwable $exception) {
            return $this->buildResult(
                $operation,
                "failed",
                null,
                $exception->getMessage() ?: "Data gagal disimpan.",
            );
        }
    }

    /**
     * Group entity server ID by module for batch submission.
     * Fetches the created model for snapshot records.
     */
    private function groupEntityPayload(string $module, int $serverId, array &$entityPayloads): void
    {
        $entityType = match ($module) {
            "rumah" => Rumah::class,
            "keluarga" => Keluarga::class,
            "penduduk" => Penduduk::class,
        };

        $record = $entityType::query()->find($serverId);

        if (!isset($entityPayloads[$module])) {
            $entityPayloads[$module] = ["ids" => [], "records" => collect()];
        }

        $entityPayloads[$module]["ids"][] = $serverId;
        if ($record) {
            $entityPayloads[$module]["records"]->push($record);
        }
    }

    public function pull(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, "SIPDT Mobile hanya tersedia untuk Admin Desa.");

        $cursor = max(0, (int) $request->integer("cursor", 0));

        // Get mobile sync operations + their batch statuses
        $changes = MobileSyncOperation::query()
            ->where("id", ">", $cursor)
            ->where("user_id", $request->user()->id)
            ->orderBy("id")
            ->limit(100)
            ->get(["id", "operation_id", "dataset", "local_id", "status", "server_id", "batch_id", "payload", "updated_at"]);

        // Enhance with batch status
        $batchIds = $changes->pluck("batch_id")->filter()->unique();
        $batches = SubmissionBatch::query()->whereIn("id", $batchIds)->get()->keyBy("id");

        $enhanced = $changes->map(function ($op) use ($batches): array {
            $batch = $batches->get($op->batch_id);
            return [
                "id" => $op->id,
                "operation_id" => $op->operation_id,
                "dataset" => $op->dataset,
                "local_id" => $op->local_id,
                "status" => $op->status,
                "batch_id" => $op->batch_id,
                "batch_status" => $batch?->status,
                "batch_status_label" => $batch ? SubmissionBatch::statusLabel($batch->status) : null,
                "server_id" => $op->server_id,
                "payload" => $op->payload,
                "updated_at" => $op->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            "changes" => $enhanced->all(),
            "next_cursor" => $changes->last()?->id ?? $cursor,
        ]);
    }

    /**
     * Fetch current batch statuses for a set of batch IDs.
     * Mobile uses this to update draft statuses when AdminKecamatan approves batches.
     */
    public function batchStatuses(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, "SIPDT Mobile hanya tersedia untuk Admin Desa.");

        $idsParam = $request->string("ids", "");
        $ids = collect(explode(",", $idsParam))
            ->map(fn (string $s) => (int) trim($s))
            ->filter(fn (int $id) => $id > 0)
            ->all();

        if ($ids === []) {
            return response()->json(["batches" => []]);
        }

        // Only allow fetching batches that belong to this user operations
        $userBatchIds = MobileSyncOperation::query()
            ->where("user_id", $request->user()->id)
            ->whereIn("batch_id", $ids)
            ->pluck("batch_id")
            ->filter()
            ->unique()
            ->all();

        $batches = SubmissionBatch::query()
            ->whereIn("id", $userBatchIds)
            ->get(["id", "status", "submission_number", "submitted_at", "verified_at", "verified_by"])
            ->map(fn (SubmissionBatch $batch): array => [
                "id" => $batch->id,
                "status" => $batch->status,
                "status_label" => SubmissionBatch::statusLabel($batch->status),
                "submission_number" => $batch->submission_number,
                "submitted_at" => $batch->submitted_at?->toIso8601String(),
                "verified_at" => $batch->verified_at?->toIso8601String(),
                "verified_by" => $batch->verifiedBy?->name,
            ])
            ->all();

        return response()->json(["batches" => $batches]);
    }

    private function storeRumah(Request $request, array $payload): int
    {
        $data = Validator::make($payload, [
            "desa_id" => ["required", "integer"],
            "dusun_id" => ["nullable", "integer", "exists:dusuns,id"],
            "kode_rumah" => ["required", "string", "max:255"],
            "alamat_lengkap" => ["required", "string"],
            "latitude" => ["required", "numeric", "between:-90,90"],
            "longitude" => ["required", "numeric", "between:-180,180"],
            "status_rumah" => ["required", "in:".implode(",", array_keys(Rumah::STATUS))],
            "keterangan" => ["nullable", "string"],
        ])->validate();

        $this->assertDesa($request, (int) $data["desa_id"]);
        if (!empty($data["dusun_id"]) && !\App\Models\Dusun::query()->whereKey($data["dusun_id"])->where("desa_id", $data["desa_id"])->exists()) {
            throw ValidationException::withMessages(["dusun_id" => "Dusun tidak berada pada desa yang dipilih."]);
        }

        return Rumah::query()->create([...$data, "created_by" => $request->user()->id])->getKey();
    }

    private function storeKeluarga(Request $request, array $payload, array &$localIdToServerId): int
    {
        $data = Validator::make($payload, [
            "rumah_id" => ["nullable", "integer"],
            "rumah_local_id" => ["nullable", "string", "max:120"],
            "nomor_kk" => ["required", "string", "max:32"],
            "nama_kepala_keluarga" => ["required", "string", "max:255"],
            "jumlah_anggota" => ["required", "integer", "min:0"],
            "status_ekonomi" => ["required", "in:".implode(",", array_keys(Keluarga::STATUS_EKONOMI))],
        ])->validate();

        $rumah = $this->resolveRumah($request, $data, $localIdToServerId);

        return Keluarga::query()->create([
            "rumah_id" => $rumah->id,
            "nomor_kk" => $data["nomor_kk"],
            "nama_kepala_keluarga" => $data["nama_kepala_keluarga"],
            "jumlah_anggota" => $data["jumlah_anggota"],
            "status_ekonomi" => $data["status_ekonomi"],
            "created_by" => $request->user()->id,
        ])->getKey();
    }

    private function storePenduduk(Request $request, array $payload, array &$localIdToServerId): int
    {
        $data = Validator::make($payload, [
            "keluarga_id" => ["nullable", "integer"],
            "keluarga_local_id" => ["nullable", "string", "max:120"],
            "nik" => ["required", "string", "max:32"],
            "nama_lengkap" => ["required", "string", "max:255"],
            "jenis_kelamin" => ["required", "in:".implode(",", array_keys(Penduduk::JENIS_KELAMIN))],
            "tempat_lahir" => ["required", "string", "max:255"],
            "tanggal_lahir" => ["required", "date"],
            "agama" => ["required", "in:".implode(",", array_keys(Penduduk::AGAMA))],
            "status_perkawinan" => ["required", "in:".implode(",", array_keys(Penduduk::STATUS_PERKAWINAN))],
            "pendidikan" => ["required", "in:".implode(",", array_keys(Penduduk::PENDIDIKAN))],
            "pekerjaan" => ["nullable", "string", "max:255"],
            "penghasilan" => ["nullable", "numeric", "min:0"],
            "status_dalam_keluarga" => ["required", "in:".implode(",", array_keys(Penduduk::STATUS_DALAM_KELUARGA))],
            "nomor_hp" => ["nullable", "string", "max:32"],
            "status_penduduk" => ["required", "in:".implode(",", array_keys(Penduduk::STATUS_PENDUDUK))],
        ])->validate();

        // SPEC-002: NIK uniqueness check per kecamatan.
        $nikExists = Penduduk::query()
            ->where("nik", $data["nik"])
            ->whereHas("keluarga.rumah.desa", function ($q) use ($request): void {
                $q->where("kecamatan_id", $request->user()->kecamatan_id);
            })
            ->exists();

        if ($nikExists) {
            throw ValidationException::withMessages([
                "nik" => "NIK sudah terdaftar di wilayah kecamatan ini.",
            ]);
        }

        $keluarga = $this->resolveKeluarga($request, $data, $localIdToServerId);

        return Penduduk::query()->create([
            "nik" => $data["nik"],
            "nama_lengkap" => $data["nama_lengkap"],
            "jenis_kelamin" => $data["jenis_kelamin"],
            "tempat_lahir" => $data["tempat_lahir"],
            "tanggal_lahir" => $data["tanggal_lahir"],
            "agama" => $data["agama"],
            "status_perkawinan" => $data["status_perkawinan"],
            "pendidikan" => $data["pendidikan"],
            "pekerjaan" => $data["pekerjaan"] ?? null,
            "penghasilan" => $data["penghasilan"] ?? null,
            "status_dalam_keluarga" => $data["status_dalam_keluarga"],
            "nomor_hp" => $data["nomor_hp"] ?? null,
            "status_penduduk" => $data["status_penduduk"],
            "keluarga_id" => $keluarga->id,
            "created_by" => $request->user()->id,
        ])->getKey();
    }

    private function resolveRumah(Request $request, array $data, array &$localIdToServerId): Rumah
    {
        // Priority 1: explicit rumah_id from payload
        $rumah = !empty($data["rumah_id"])
            ? Rumah::query()->find($data["rumah_id"])
            : null;

        // Priority 2: rumah_local_id from same push session
        if (!$rumah && !empty($data["rumah_local_id"])) {
            if (isset($localIdToServerId[$data["rumah_local_id"]])) {
                $rumah = Rumah::query()->find($localIdToServerId[$data["rumah_local_id"]]);
            }
            // Fallback: check previous sync operations
            if (!$rumah) {
                $rumah = Rumah::query()->find($this->serverId($request, "rumah", $data["rumah_local_id"]));
            }
        }

        if (!$rumah || (int) $rumah->desa_id !== (int) $request->user()->desa_id) {
            throw ValidationException::withMessages(["rumah_id" => "Rumah tidak ditemukan pada desa Admin Desa ini."]);
        }

        return $rumah;
    }

    private function resolveKeluarga(Request $request, array $data, array &$localIdToServerId): Keluarga
    {
        // Priority 1: explicit keluarga_id from payload
        $keluarga = !empty($data["keluarga_id"])
            ? Keluarga::query()->with("rumah")->find($data["keluarga_id"])
            : null;

        // Priority 2: keluarga_local_id from same push session
        if (!$keluarga && !empty($data["keluarga_local_id"])) {
            if (isset($localIdToServerId[$data["keluarga_local_id"]])) {
                $keluarga = Keluarga::query()->with("rumah")->find($localIdToServerId[$data["keluarga_local_id"]]);
            }
            // Fallback: check previous sync operations
            if (!$keluarga) {
                $keluarga = Keluarga::query()->with("rumah")->find($this->serverId($request, "keluarga", $data["keluarga_local_id"]));
            }
        }

        if (!$keluarga || (int) $keluarga->rumah?->desa_id !== (int) $request->user()->desa_id) {
            throw ValidationException::withMessages(["keluarga_id" => "KK tidak ditemukan pada desa Admin Desa ini."]);
        }

        return $keluarga;
    }

    private function serverId(Request $request, string $dataset, string $localId): ?int
    {
        return MobileSyncOperation::query()
            ->where("user_id", $request->user()->id)
            ->where("dataset", $dataset)
            ->where("local_id", $localId)
            ->where("status", "accepted")
            ->value("server_id");
    }

    private function assertDesa(Request $request, int $desaId): void
    {
        if ($desaId !== (int) $request->user()->desa_id) {
            throw ValidationException::withMessages(["desa_id" => "Data hanya boleh dibuat pada desa akun Admin Desa."]);
        }
    }

    /**
     * Build a result array for a single operation.
     */
    private function buildResult(array $operation, string $status, ?int $serverId, ?string $message = null, ?int $batchId = null): array
    {
        return array_filter([
            "operation_id" => $operation["operation_id"],
            "local_id" => $operation["local_id"],
            "status" => $status,
            "server_id" => $serverId,
            "batch_id" => $batchId,
            "message" => $message,
        ], static fn ($value) => $value !== null);
    }

    /**
     * Build the JSON response.
     */
    private function buildResponse(
        $operationResults,
        ?SubmissionBatch $submittedBatch,
        ?\Illuminate\Support\Collection $batchesById,
        ?string $error = null,
    ): JsonResponse {
        $responseBatchId = $submittedBatch?->id ?? $operationResults->pluck("batch_id")->filter()->first();
        $responseBatch = $submittedBatch;

        $data = [
            "results" => $operationResults->values()->all(),
            "batch_id" => $responseBatchId,
            "batch_status" => $responseBatch?->status,
            "batch_status_label" => $responseBatch ? SubmissionBatch::statusLabel($responseBatch->status) : null,
            "server_time" => Carbon::now()->toIso8601String(),
        ];

        if ($error !== null) {
            $data["error"] = $error;
        }

        return response()->json($data);
    }
}