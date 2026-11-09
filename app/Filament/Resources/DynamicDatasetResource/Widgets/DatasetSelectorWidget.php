<?php

namespace App\Filament\Resources\DynamicDatasetResource\Widgets;

use App\Models\Dataset;
use App\Models\Opd;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DatasetSelectorWidget extends Widget
{
    protected string $view = 'filament.resources.dynamic-dataset-resource.widgets.dataset-selector';
    protected int | string | array $columnSpan = 'full';

    public function getDatasets()
    {
        $user = Auth::user();
        $query = Dataset::with(['opd', 'fields:id,dataset_id'])->where('is_active', true)->orderBy('nama');

        // Filter by user's OPD (kecuali super admin)
        if ($user && ! ($user->isSuperAdmin() || $user->isWalidata())) {
            $opdId = $user->opd_id;
            if ($opdId) {
                $query->where('opd_id', $opdId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->get()->groupBy(fn ($d) => $d->opd?->nama ?? 'Lainnya');
    }
}
