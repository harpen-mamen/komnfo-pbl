<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendudukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'keluarga_id' => $this->keluarga_id,
            'nik' => $this->nik,
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'umur' => $this->umur,
            'agama' => $this->agama,
            'status_perkawinan' => $this->status_perkawinan,
            'pendidikan' => $this->pendidikan,
            'pekerjaan' => $this->pekerjaan,
            'penghasilan' => $this->penghasilan ? (float) $this->penghasilan : null,
            'status_dalam_keluarga' => $this->status_dalam_keluarga,
            'nomor_hp' => $this->nomor_hp,
            'status_penduduk' => $this->status_penduduk,
            'rumah' => new RumahResource($this->whenLoaded('rumah')),
            'keluarga' => new KeluargaResource($this->whenLoaded('keluarga')),
            'has_batch' => $this->latestSubmissionBatchItem?->submissionBatch !== null,
            'batch_status' => $this->latestSubmissionBatchItem?->submissionBatch?->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
