<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class LeafletGeometryField extends Field
{
    protected string $view = 'filament.forms.components.leaflet-geometry-field';

    protected string $geometryType = 'any';

    public function geometryType(string $type): static
    {
        $this->geometryType = in_array($type, ['point', 'linestring', 'polygon', 'any'], true) ? $type : 'any';
        $this->viewData(['geometryType' => $this->geometryType]);
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->columnSpanFull();
        $this->viewData([
            'geometryType' => $this->geometryType,
            'mapId' => 'dataset-map-' . substr(spl_object_hash($this), 0, 12),
        ]);
    }
}
