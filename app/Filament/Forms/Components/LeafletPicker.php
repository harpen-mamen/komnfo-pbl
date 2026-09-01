<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class LeafletPicker extends Field
{
    protected string $view = 'filament.forms.components.leaflet-picker';

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewData([
            'lpId' => substr(spl_object_hash($this), 0, 12),
        ]);
    }
}
