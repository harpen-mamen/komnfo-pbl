<?php

namespace App\Filament\Resources\DATA_SEKTORAL\DatasetResource\Pages;

use App\Filament\Resources\DATA_SEKTORAL\DatasetResource;
use Filament\Resources\Pages\EditRecord;

abstract class EditDataset extends EditRecord
{
    protected static string $resource = DatasetResource::class;
}
