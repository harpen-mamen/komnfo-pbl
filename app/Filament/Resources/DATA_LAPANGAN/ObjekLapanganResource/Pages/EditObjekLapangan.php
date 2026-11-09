<?php

namespace App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource\Pages;

use App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource;
use Filament\Resources\Pages\EditRecord;

abstract class EditObjekLapangan extends EditRecord
{
    protected static string $resource = ObjekLapanganResource::class;
}
