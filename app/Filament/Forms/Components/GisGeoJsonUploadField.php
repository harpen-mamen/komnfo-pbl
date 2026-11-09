<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class GisGeoJsonUploadField extends Field
{
    protected string $view = 'filament.forms.components.gis-geojson-upload-field';

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewData(['uploadId' => 'gis-upload-' . substr(spl_object_hash($this), 0, 12)]);
    }
}
