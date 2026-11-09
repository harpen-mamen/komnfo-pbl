<?php

namespace App\Filament\Resources\PortalContentResource\Pages;

use App\Filament\Resources\PortalContentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePortalContent extends CreateRecord
{
    protected static string $resource = PortalContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['published_at'] = ! empty($data['is_published']) ? ($data['published_at'] ?? now()) : null;
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->keepSinglePublishedHero();
    }

    private function keepSinglePublishedHero(): void
    {
        if ($this->record->type === 'hero' && $this->record->is_published) {
            $this->record->newQuery()->where('type', 'hero')->whereKeyNot($this->record->getKey())->update(['is_published' => false]);
        }
    }
}
