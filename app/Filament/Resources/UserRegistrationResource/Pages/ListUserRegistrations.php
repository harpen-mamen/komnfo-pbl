<?php

namespace App\Filament\Resources\UserRegistrationResource\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\UserRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserRegistrations extends ListRecords
{
    protected static string $resource = UserRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    return redirect(request()->url());
                }),
        ];
    }
}
