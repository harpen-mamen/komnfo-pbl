<?php

namespace App\Filament\Resources\UserRegistrationResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\UserRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserRegistration extends EditRecord
{
    protected static string $resource = UserRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
