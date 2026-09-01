<?php

namespace App\Http\Resources;

use App\Models\SubmissionBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionBatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'module' => $this->module,
            'module_label' => SubmissionBatch::moduleLabel($this->module),
            'submission_number' => $this->submission_number,
            'kecamatan_id' => $this->kecamatan_id,
            'kecamatan' => $this->whenLoaded('kecamatan', fn () => [
                'id' => $this->kecamatan->id,
                'nama' => $this->kecamatan->nama,
            ]),
            'periode_bulan' => $this->periode_bulan,
            'semester' => $this->semester,
            'periode_tahun' => $this->periode_tahun,
            'status' => $this->status,
            'status_label' => SubmissionBatch::statusLabel($this->status),
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'createdBy' => $this->whenLoaded('createdBy', fn () => new UserResource($this->createdBy)),
            'verified_by' => $this->verified_by,
            'verifiedBy' => $this->whenLoaded('verifiedBy', fn () => new UserResource($this->verifiedBy)),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'items_count' => $this->when(isset($this->items_count), $this->items_count),
        ];
    }
}
