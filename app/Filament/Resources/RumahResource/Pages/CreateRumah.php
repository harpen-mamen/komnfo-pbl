<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use App\Support\AdminKecamatan;
use Filament\Resources\Pages\CreateRecord;

class CreateRumah extends CreateRecord
{
    protected static string $resource = RumahResource::class;

    public ?array $koordinat = ['latitude' => '', 'longitude' => ''];

    public function setKoordinat(?string $latitude, ?string $longitude): void
    {
        $this->koordinat = [
            'latitude' => $latitude ?? '',
            'longitude' => $longitude ?? '',
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort_unless(AdminKecamatan::canUseDesa((int) ($data['desa_id'] ?? 0)), 403);

        // Ambil koordinat dari component property $koordinat (Livewire)
        // karena LeafletPicker menulis ke property ini, bukan ke form schema data
        $koordinat = $this->koordinat ?? [];
        if (is_array($koordinat) && (isset($koordinat['latitude']) || isset($koordinat['longitude']))) {
            $data['latitude'] = $koordinat['latitude'] ?: null;
            $data['longitude'] = $koordinat['longitude'] ?: null;
        }
        unset($data['koordinat']);

        return $data;
    }
}
