<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use App\Support\AdminKecamatan;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRumah extends EditRecord
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

    protected function getActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(AdminKecamatan::canUseDesa((int) ($data['desa_id'] ?? 0)), 403);

        // Ambil koordinat dari component property $koordinat (Livewire)
        $koordinat = $this->koordinat ?? [];
        if (is_array($koordinat) && (isset($koordinat['latitude']) || isset($koordinat['longitude']))) {
            $data['latitude'] = $koordinat['latitude'] ?: null;
            $data['longitude'] = $koordinat['longitude'] ?: null;
        }
        unset($data['koordinat']);

        return $data;
    }

    protected function fillForm(): void
    {
        $record = $this->getRecord();

        // Build data from record + our koordinat accessor
        $data = $record->attributesToArray();
        $data['koordinat'] = [
            'latitude' => (string) ($record->latitude ?? ''),
            'longitude' => (string) ($record->longitude ?? ''),
        ];

        $data = $this->mutateFormDataBeforeFill($data);

        $this->form->fill($data);
    }
}
