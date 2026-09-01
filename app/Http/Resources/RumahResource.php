<?php

namespace App\Http\Resources;

use App\Http\Resources\KeluargaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RumahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'desa_id' => $this->desa_id,
            'dusun_id' => $this->dusun_id,
            'kode_rumah' => $this->kode_rumah,
            'alamat_lengkap' => $this->alamat_lengkap,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'status_rumah' => $this->status_rumah,
            'keterangan' => $this->keterangan,
            'rumah_count' => $this->when($this->keluargas_count !== null, $this->keluargas_count),
            'keluargas' => KeluargaResource::collection($this->whenLoaded('keluargas')),
            'has_batch' => $this->latestSubmissionBatchItem?->submissionBatch !== null,
            'batch_status' => $this->latestSubmissionBatchItem?->submissionBatch?->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
