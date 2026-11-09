<?php

namespace App\Filament\Widgets\Opd;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OpdSektoralStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.opd-sektoral-stats';
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $user = Auth::user();
        $query = DatasetValue::query();
        $opdQuery = Opd::where('aktif', true);

        if (! $user) {
            return [];
        }

        if (! ($user->isSuperAdmin() || $user->isWalidata())) {
            if ($user->opd_id) {
                $query->whereIn('dataset_id', Dataset::query()->select('id')->where('opd_id', $user->opd_id)->whereNull('deleted_at'));
                $opdQuery->where('id', $user->opd_id);
            } else {
                return [];
            }
        }

        $stats = [
            'total_opd' => $opdQuery->count(),
            'total_datasets' => Dataset::whereNull('deleted_at')->where('is_active', true)
                ->when(! ($user->isSuperAdmin() || $user->isWalidata()) && $user->opd_id,
                    fn ($q) => $q->where('opd_id', $user->opd_id))
                ->count(),
            'total_values' => $query->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'submitted' => (clone $query)->where('status', 'submitted')->count(),
            'verified' => (clone $query)->where('status', 'verified')->count(),
            'published' => (clone $query)->where('status', 'published')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'revision' => (clone $query)->where('status', 'revision')->count(),
        ];

        // Top 5 OPD by data (untuk walidata)
        if ($user->isSuperAdmin() || $user->isWalidata()) {
            $topOpds = DatasetValue::query()
                ->join('datasets', 'datasets.id', '=', 'dataset_values.dataset_id')
                ->join('opds', 'opds.id', '=', 'datasets.opd_id')
                ->whereNull('datasets.deleted_at')
                ->selectRaw('opds.nama as nama, opds.kode as kode, COUNT(*) as total')
                ->groupBy('opds.id', 'opds.nama', 'opds.kode')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
            $stats['top_opds'] = $topOpds;
        }

        return $stats;
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
