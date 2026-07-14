<?php

namespace App\Filament\Widgets;

use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class KecamatanWorkflowOverview extends ChartWidget
{
    protected static ?string $heading = 'Status Pendataan Kecamatan';

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected function getData(): array
    {
        if (! Schema::hasTable('submission_batches')) {
            $labels = [
                SubmissionBatch::STATUS_DRAFT => 'Draft',
                SubmissionBatch::STATUS_PENDING => 'Menunggu Verifikasi',
                SubmissionBatch::STATUS_IN_REVIEW => 'Sedang Diverifikasi',
                SubmissionBatch::STATUS_REVISION => 'Perlu Revisi',
                SubmissionBatch::STATUS_APPROVED => 'Disetujui',
            ];

            return [
                'datasets' => [
                    ['label' => 'Jumlah', 'data' => array_fill(0, count($labels), 0)],
                ],
                'labels' => array_values($labels),
            ];
        }

        $query = \App\Models\SubmissionBatch::query()
            ->where('kecamatan_id', PanelAccess::user()?->kecamatan_id);

        $labels = [
            SubmissionBatch::STATUS_DRAFT => 'Draft',
            SubmissionBatch::STATUS_PENDING => 'Menunggu Verifikasi',
            SubmissionBatch::STATUS_IN_REVIEW => 'Sedang Diverifikasi',
            SubmissionBatch::STATUS_REVISION => 'Perlu Revisi',
            SubmissionBatch::STATUS_APPROVED => 'Disetujui',
        ];

        $data = collect($labels)->mapWithKeys(fn ($label, $code) => [
            $label => (clone $query)->where('status', $code)->count(),
        ]);

        return [
            'datasets' => [
                ['label' => 'Jumlah', 'data' => $data->values()],
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
