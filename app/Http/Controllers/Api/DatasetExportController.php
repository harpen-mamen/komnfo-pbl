<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Services\DataInput\DatasetExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetExportController extends Controller
{
    public function __construct(private DatasetExportService $export)
    {
    }

    /**
     * Export data values for a dataset.
     */
    public function export(Request $request, string $kode)
    {
        $user = Auth::user();
        $dataset = Dataset::where('kode', $kode)->whereNull('deleted_at')->firstOrFail();

        // Check permission: walidata sees all, OPD users only their own
        if (! ($user->isSuperAdmin() || $user->isWalidata())) {
            if (! $user->opd_id || $user->opd_id !== $dataset->opd_id) {
                abort(403, 'Anda tidak memiliki akses ke dataset ini.');
            }
        }

        $format = $request->input('format', 'csv');
        $status = $request->input('status');

        $filename = $dataset->kode . '_' . date('Ymd_His') . '.' . $format;

        switch ($format) {
            case 'json':
                $content = $this->export->toJson($dataset, $status);
                return response($content, 200, [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);

            case 'xls':
            case 'excel':
                $content = $this->export->toExcelHtml($dataset, $status);
                return response($content, 200, [
                    'Content-Type' => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);

            case 'csv':
            default:
                $content = $this->export->toCsv($dataset, $status);
                return response($content, 200, [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
        }
    }

    /**
     * Get status breakdown for a dataset (for dashboard).
     */
    public function stats(string $kode)
    {
        $user = Auth::user();
        $dataset = Dataset::where('kode', $kode)->whereNull('deleted_at')->firstOrFail();

        if (! ($user->isSuperAdmin() || $user->isWalidata())) {
            if (! $user->opd_id || $user->opd_id !== $dataset->opd_id) {
                abort(403);
            }
        }

        return response()->json([
            'dataset' => [
                'kode' => $dataset->kode,
                'nama' => $dataset->nama,
                'opd' => $dataset->opd?->nama,
            ],
            'breakdown' => $this->export->getStatusBreakdown($dataset),
        ]);
    }
}
