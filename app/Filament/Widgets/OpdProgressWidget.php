<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\Widget;

/**
 * OpdProgressWidget — progress dataset per OPD.
 */
class OpdProgressWidget extends Widget
{
    protected string $view = 'filament.widgets.opd-progress';

    /** Full-width. */
    protected int|string|array $columnSpan = 'full';

    /** @var array<int, array{nama:string,total_dataset:int}> */
    public array $opdProgress = [];

    public function mount(): void
    {
        $units = Unit::withCount('datasets')->opd()->get();

        $this->opdProgress = $units->map(function ($unit) {
            return [
                'nama' => $unit->nama,
                'total_dataset' => $unit->datasets_count,
            ];
        })->toArray();
    }
}
