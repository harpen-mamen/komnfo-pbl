<?php

namespace App\Filament\Resources\PortalContentResource\Pages;

use App\Filament\Resources\PortalContentResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPortalContent extends EditRecord
{
    protected static string $resource = PortalContentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        $data['published_at'] = ! empty($data['is_published']) ? ($data['published_at'] ?? now()) : null;
        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->type === 'hero' && $this->record->is_published) {
            $this->record->newQuery()->where('type', 'hero')->whereKeyNot($this->record->getKey())->update(['is_published' => false]);
        }
    }
}
