<?php

namespace App\Filament\Resources\UserRegistrationResource\Pages;

use App\Filament\Resources\UserRegistrationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserRegistrations extends ListRecords
{
    protected static string $resource = UserRegistrationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-refresh')
                ->action(function () {
                    return redirect(request()->url());
                }),
        ];
    }
}