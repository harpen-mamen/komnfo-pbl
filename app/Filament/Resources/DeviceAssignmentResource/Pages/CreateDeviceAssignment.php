<?php
namespace App\Filament\Resources\DeviceAssignmentResource\Pages;
use App\Filament\Resources\DeviceAssignmentResource;
use Filament\Resources\Pages\CreateRecord;
class CreateDeviceAssignment extends CreateRecord
{
    protected static string $resource = DeviceAssignmentResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['activated_at'] = now();
        return $data;
    }
}
