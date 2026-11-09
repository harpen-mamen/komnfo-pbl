<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\DatasetSubmission;
use App\Models\DatasetValue;
use App\Models\User;
use App\Notifications\BatchNotification;
use App\Services\DataInput\DatasetValueManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DatasetSubmissionReviewController extends Controller
{
    public function verify(Request $request, int $submissionId, DatasetValueManager $manager)
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->isWalidata() || $actor->isSuperAdmin()), 403);

        try {
            $submission = DB::transaction(function () use ($submissionId, $actor, $manager) {
                $submission = DatasetSubmission::query()->with('values')->lockForUpdate()->findOrFail($submissionId);
                abort_unless($submission->status === DatasetValue::STATUS_SUBMITTED, 422, 'Paket ini sudah diproses.');
                abort_if($submission->values->isEmpty(), 422, 'Paket tidak berisi data.');

                foreach ($submission->values as $value) {
                    $manager->verify($value, 'Diverifikasi oleh Kominfo dalam paket #' . $submission->id, false);
                    $manager->publish($value);
                }

                $submission->update(['status' => DatasetValue::STATUS_PUBLISHED]);
                $this->notifyOpd($submission, $actor, DatasetValue::STATUS_PUBLISHED);

                return $submission;
            });

            return redirect()->route('filament.admin.pages.verifikasi-data-sektoral')->with(
                'review_success',
                sprintf('Paket #%d berhasil diverifikasi dan %d data telah diterbitkan untuk infografis.', $submission->id, $submission->values()->count())
            );
        } catch (Throwable $exception) {
            Log::error('Dataset submission verification failed', [
                'submission_id' => $submissionId,
                'actor_id' => $actor->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->with('review_error', 'Verifikasi gagal: ' . $exception->getMessage());
        }
    }

    public function requestRevision(Request $request, int $submissionId, DatasetValueManager $manager)
    {
        $actor = $request->user();
        abort_unless($actor && ($actor->isWalidata() || $actor->isSuperAdmin()), 403);
        $validated = $request->validate(['notes' => ['required', 'string', 'max:2000']]);

        try {
            $submission = DB::transaction(function () use ($submissionId, $actor, $manager, $validated) {
                $submission = DatasetSubmission::query()->with('values')->lockForUpdate()->findOrFail($submissionId);
                abort_unless($submission->status === DatasetValue::STATUS_SUBMITTED, 422, 'Paket ini sudah diproses.');

                foreach ($submission->values as $value) {
                    $manager->requestRevision($value, $validated['notes'], false);
                    $value->dataset_submission_id = null;
                    $value->save();
                }

                $submission->update(['status' => DatasetValue::STATUS_REVISION]);
                $this->notifyOpd($submission, $actor, DatasetValue::STATUS_REVISION, $validated['notes']);
                return $submission;
            });

            return redirect()->route('filament.admin.pages.verifikasi-data-sektoral')->with(
                'review_success', 'Paket #' . $submission->id . ' dikembalikan ke OPD untuk revisi.'
            );
        } catch (Throwable $exception) {
            Log::error('Dataset submission revision request failed', [
                'submission_id' => $submissionId,
                'actor_id' => $actor->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->with('review_error', 'Permintaan revisi gagal: ' . $exception->getMessage());
        }
    }

    private function notifyOpd(DatasetSubmission $submission, User $actor, string $status, ?string $notes = null): void
    {
        $recipients = User::query()->where('opd_id', $submission->opd_id)->get()
            ->reject(fn (User $user) => (int) $user->id === (int) $actor->id);
        if ($recipients->isEmpty()) return;

        $approved = $status === DatasetValue::STATUS_PUBLISHED;
        Notification::sendNow($recipients, new BatchNotification(
            $approved ? 'Data OPD diverifikasi dan diterbitkan' : 'Data OPD perlu revisi',
            sprintf(
                'Paket #%d (%d data) %s oleh Kominfo.%s',
                $submission->id,
                $submission->values()->count(),
                $approved ? 'telah diverifikasi dan diterbitkan' : 'dikembalikan untuk revisi',
                $notes ? ' Catatan: ' . $notes : ''
            ),
            'DatasetSubmission',
            $submission->id,
            $actor->id,
            $actor->name
        ));
    }
}
