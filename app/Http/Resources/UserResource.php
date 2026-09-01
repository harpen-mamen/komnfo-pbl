<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name')->values()->all(),
            'role_label' => $this->role_label,
            'kecamatan_id' => $this->kecamatan_id,
            'kecamatan' => $this->whenLoaded('kecamatan', fn () => [
                'id' => $this->kecamatan->id,
                'nama' => $this->kecamatan->nama,
            ]),
            'desa_id' => $this->desa_id,
            'desa' => $this->whenLoaded('desa', fn () => [
                'id' => $this->desa->id,
                'nama' => $this->desa->nama,
            ]),
            'nip' => $this->nip,
            'jabatan' => $this->jabatan,
            'no_hp' => $this->no_hp,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
