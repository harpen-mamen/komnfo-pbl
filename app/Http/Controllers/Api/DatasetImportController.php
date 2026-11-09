<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\DatasetValueCsvImport;
use App\Imports\DatasetValueImport;
use App\Models\Dataset;
use App\Services\DataInput\DatasetValueManager;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;

/**
 * DatasetImportController - Handle bulk import data via CSV.
 *
 * Routes:
 * - GET /admin/dataset/{kode}/import-template -> Download CSV template
 * - POST /admin/dataset/{kode}/import -> Upload & import data
 */
class DatasetImportController extends Controller
{
    public function __construct(private DatasetValueManager $manager) {}

    /**
     * Download CSV template
     */
    public function template(string $kode)
    {
        $dataset = Dataset::where('kode', $kode)->whereNull('deleted_at')->firstOrFail();
        $headers = DatasetValueCsvImport::getTemplateHeaders($dataset);
        $sample = DatasetValueCsvImport::getTemplateSample($dataset);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers, ',', '"', '');
        fputcsv($output, $sample, ',', '"', '');
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"template-{$kode}-" . date('Ymd') . ".csv\"",
        ]);
    }

    /**
     * Import from uploaded CSV file
     */
    public function import(Request $request, string $kode)
    {
        $user = auth()->user();
        $dataset = Dataset::where('kode', $kode)->whereNull('deleted_at')->firstOrFail();

        if (! ($user->isSuperAdmin() || $user->isWalidata())) {
            if (! $user->opd_id || $user->opd_id !== $dataset->opd_id) {
                abort(403, 'Anda tidak memiliki akses ke dataset ini.');
            }
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'auto_publish' => 'nullable|boolean',
        ]);

        $autoPublish = $request->boolean('auto_publish') && ($user->isSuperAdmin() || $user->isWalidata());
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $import = new DatasetValueImport($dataset, $user, $autoPublish);
            app(Excel::class)->import($import, $file->getRealPath(), null, $extension === 'xlsx' ? Excel::XLSX : Excel::XLS);
            $result = $import->result;
        } else {
            $import = new DatasetValueCsvImport($dataset, $user, $autoPublish);
            $result = $import->import($file->getRealPath());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $result['failed'] === 0 && $result['errors'] === [],
                'message' => "Import selesai. {$result['success']} berhasil, {$result['failed']} gagal.",
                'data' => $result,
            ]);
        }

        return back()->with('import_result', $result);
    }
}
