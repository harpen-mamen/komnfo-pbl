<?php

namespace App\Filament\Widgets;

use App\Models\DatasetSubmission;
use App\Models\DatasetValue;
use App\Support\PanelAccess;
use Filament\Widgets\Widget;

class PendingDatasetSubmissionsWidget extends Widget
{
    protected string $view = 'filament.widgets.pending-dataset-submissions-widget';
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    protected function getViewData(): array
    {
        $submissions = DatasetSubmission::query()
            ->with(['opd', 'createdBy'])
            ->withCount('values')
            ->where('status', DatasetValue::STATUS_SUBMITTED)
            ->latest('submitted_at')
            ->get();

        return [
            'submissions' => $submissions,
            'pendingCount' => $submissions->count(),
            'url' => route('filament.admin.pages.verifikasi-data-sektoral'),
        ];
    }
}
