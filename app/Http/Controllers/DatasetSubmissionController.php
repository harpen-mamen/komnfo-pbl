<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\DatasetValue;
use App\Services\DataInput\DatasetValueManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DatasetSubmissionController extends Controller
{
    public function store(Request $request, DatasetValueManager $manager)
    {
        $user = $request->user();
        abort_unless($user?->opd_id && ($user->isOpdAdmin() || $user->isKepalaDinas()), 403);

        $validated = $request->validate([
            'selectedValues' => ['nullable', 'array'],
            'selectedValues.*' => ['integer', 'distinct'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $ids = array_values(array_unique($validated['selectedValues'] ?? []));
        if ($ids === []) {
            $ids = DatasetValue::query()
                ->whereHas('dataset', fn ($query) => $query->where('opd_id', $user->opd_id))
                ->whereIn('status', [DatasetValue::STATUS_DRAFT, DatasetValue::STATUS_REVISION])
                ->pluck('id')->all();
        }

        if ($ids === []) {
            return back()->with('submission_error', 'Tidak ada data draft atau revisi yang siap dikirim.');
        }

        try {
            $submission = $manager->submitMany($ids, $user, $validated['notes'] ?? null);

            return redirect()
                ->route('filament.admin.pages.kirim-data-sektoral')
                ->with('submission_success', sprintf(
                    'Data berhasil dikirim ke Kominfo. %d data masuk dalam paket #%d dan menunggu verifikasi.',
                    count($ids),
                    $submission->id
                ));
        } catch (Throwable $exception) {
            Log::error('OPD dataset submission failed', [
                'user_id' => $user->id,
                'dataset_value_ids' => $ids,
                'exception' => $exception->getMessage(),
            ]);

            return back()->with('submission_error', $exception instanceof ValidationException
                ? 'Periksa catatan pengiriman dan coba lagi.'
                : 'Pengiriman gagal: ' . $exception->getMessage());
        }
    }
}
