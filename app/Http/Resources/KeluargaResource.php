<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeluargaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rumah_id' => $this->rumah_id,
            'nomor_kk' => $this->nomor_kk,
            'nama_kepala_keluarga' => $this->nama_kepala_keluarga,
            'jumlah_anggota' => $this->jumlah_anggota,
            'status_ekonomi' => $this->status_ekonomi,
            'rumah' => new RumahResource($this->whenLoaded('rumah')),
            'penduduks' => PendudukResource::collection($this->whenLoaded('penduduks')),
            'has_batch' => $this->latestSubmissionBatchItem?->submissionBatch !== null,
            'batch_status' => $this->latestSubmissionBatchItem?->submissionBatch?->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
