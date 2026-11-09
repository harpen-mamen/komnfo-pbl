<?php

namespace App\Filament\Resources\DATA_SEKTORAL\DatasetResource\Pages;

use App\Filament\Resources\DATA_SEKTORAL\DatasetResource;
use Filament\Resources\Pages\ViewRecord;

abstract class ViewDataset extends ViewRecord
{
    protected static string $resource = DatasetResource::class;
}
