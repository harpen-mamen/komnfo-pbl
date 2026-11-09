<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class LeafletLineDrawer extends Field
{
    protected string $view = 'filament.forms.components.leaflet-line-drawer';

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewData([
            'drawerId' => substr(spl_object_hash($this), 0, 12),
        ]);
    }
}
