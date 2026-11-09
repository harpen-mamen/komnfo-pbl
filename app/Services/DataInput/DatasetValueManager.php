<?php

namespace App\Services\DataInput;

use LogicException;
use Throwable;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\DatasetSubmission;
use App\Models\User;
use App\Notifications\BatchNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DatasetValueManager
{
    public function submitMany(array $valueIds, User $actor, ?string $notes = null): DatasetSubmission
    {
        abort_unless($actor->opd_id && ($actor->isOpdAdmin() || $actor->isKepalaDinas()), 403);
        $values = DatasetValue::query()->with('dataset')->whereIn('id', array_unique($valueIds))->lockForUpdate()->get();
        abort_if($values->isEmpty() || $values->count() !== count(array_unique($valueIds)), 422, 'Data yang dipilih tidak ditemukan.');

        foreach ($values as $value) {
            abort_unless((int) $value->dataset?->opd_id === (int) $actor->opd_id, 403);
            abort_unless(in_array($value->status, [DatasetValue::STATUS_DRAFT, DatasetValue::STATUS_REVISION], true), 422, 'Hanya data draft atau revisi yang dapat dikirim.');
        }

        return DB::transaction(function () use ($values, $actor, $notes) {
            $submission = DatasetSubmission::create([
                'opd_id' => $actor->opd_id,
                'created_by' => $actor->id,
                'status' => DatasetValue::STATUS_SUBMITTED,
                'notes' => $notes,
                'submitted_at' => now(),
            ]);

            foreach ($values as $value) {
                $previous = $value->status;
                $value->status = DatasetValue::STATUS_SUBMITTED;
                $value->status_verifikasi = DatasetValue::STATUS_SUBMITTED;
                $value->dataset_submission_id = $submission->id;
                // Keep the source and producer caveat attached to each row for review.
                // The package note remains available for a shared submission-level note.
                $value->save();
                $this->logWorkflow($value, $previous, DatasetValue::STATUS_SUBMITTED, 'Dikirim dalam pengiriman #' . $submission->id, $actor->id);
            }

            $this->notifyDatasetSubmission($submission, $actor);
            return $submission;
        });
    }

    public function createDraft(Dataset $dataset, array $data): DatasetValue
    {
        $this->assertProducerAccess($dataset);
        abort_unless($dataset->is_active, 403);
        $data = $this->normalizeInput($dataset,$data);
        $rules = DatasetFieldResolver::buildValidationRules($dataset);
        $validated = validator($data, $rules)->validate();

        $value = $this->buildValue($dataset, $validated);
        $value->status = DatasetValue::STATUS_DRAFT;
        $value->status_verifikasi = DatasetValue::STATUS_DRAFT;
        $value->save();

        return $value;
    }

    public function updateDraft(DatasetValue $value, array $data): DatasetValue
    {
        $this->assertProducerAccess($value->dataset);
        if (! in_array($value->status, ['draft', 'revision'], true)) {
            throw new LogicException('Data dengan status ' . $value->status . ' tidak dapat diubah.');
        }

        $data = $this->normalizeInput($value->dataset,$data);
        $rules = DatasetFieldResolver::buildValidationRules($value->dataset);
        $validated = validator($data, $rules)->validate();

        $this->applyData($value, $validated);
        $value->updated_by = Auth::id();
        $value->save();

        return $value;
    }

    public function submit(DatasetValue $value): bool
    {
        $this->assertProducerAccess($value->dataset);
        abort_unless(Auth::user()?->isOpdAdmin() || Auth::user()?->isKepalaDinas(), 403);
        if ($value->status !== DatasetValue::STATUS_DRAFT) {
            throw new LogicException('Hanya data draft yang dapat diajukan.');
        }

        $value->status = DatasetValue::STATUS_SUBMITTED;
        $value->status_verifikasi = DatasetValue::STATUS_SUBMITTED;
        $value->save();

        $this->logWorkflow($value, null, DatasetValue::STATUS_SUBMITTED, 'Diajukan ke Walidata untuk verifikasi.');
        $this->notifyDatasetStakeholders($value, 'submitted');
        return true;
    }

    public function verify(DatasetValue $value, ?string $notes = null, bool $notify = true): bool
    {
        $walidata = Auth::user();
        abort_unless($walidata && ($walidata->isWalidata() || $walidata->isSuperAdmin()), 403);
        if ($value->status !== DatasetValue::STATUS_SUBMITTED) {
            throw new LogicException('Hanya data submitted yang dapat diverifikasi.');
        }

        $value->status = DatasetValue::STATUS_VERIFIED;
        $value->status_verifikasi = DatasetValue::STATUS_VERIFIED;
        $value->verified_at = now();
        $value->verified_by = $walidata->id;
        // Keep the source marker and source attribution visible after review.
        // Review commentary is appended so dashboards can still resolve the source URL.
        $sourceNotes = (string) $value->notes;
        if ($notes !== null && $notes !== '' && preg_match('/^\[(?:PUBLIC-SOURCE|SANGIHE-SOURCE|STATISTIK-SEKTORAL-2024|DEMO-OPD):?[^\]]*\]/', $sourceNotes)) {
            $value->notes = $sourceNotes . ' ' . $notes;
        } else {
            $value->notes = $notes ?? $value->notes;
        }
        $value->save();

        $this->logWorkflow($value, DatasetValue::STATUS_SUBMITTED, DatasetValue::STATUS_VERIFIED, $notes, $walidata->id);
        if ($notify) $this->notifyDatasetStakeholders($value, 'verified');
        return true;
    }

    public function requestRevision(DatasetValue $value, string $notes, bool $notify = true): bool
    {
        $walidata = Auth::user();
        abort_unless($walidata && ($walidata->isWalidata() || $walidata->isSuperAdmin()), 403);
        if ($value->status !== DatasetValue::STATUS_SUBMITTED) {
            throw new LogicException('Hanya data submitted yang dapat diminta revisinya.');
        }

        $previous = $value->status;
        $value->status = DatasetValue::STATUS_REVISION;
        $value->status_verifikasi = DatasetValue::STATUS_REVISION;
        $value->notes = $notes;
        $value->save();
        $this->logWorkflow($value, $previous, DatasetValue::STATUS_REVISION, $notes, $walidata->id);
        if ($notify) $this->notifyDatasetStakeholders($value, 'revision');

        return true;
    }

    public function publish(DatasetValue $value): bool
    {
        abort_unless(Auth::user() && (Auth::user()->isWalidata() || Auth::user()->isSuperAdmin()), 403);
        if ($value->status !== DatasetValue::STATUS_VERIFIED) {
            throw new LogicException('Hanya data verified yang dapat dipublikasikan.');
        }

        $value->status = DatasetValue::STATUS_PUBLISHED;
        $value->status_verifikasi = DatasetValue::STATUS_PUBLISHED;
        $value->published_at = now();
        $value->save();

        $this->logWorkflow($value, DatasetValue::STATUS_VERIFIED, DatasetValue::STATUS_PUBLISHED, 'Dipublikasikan ke masyarakat.');
        return true;
    }

    public function reject(DatasetValue $value, string $notes): bool
    {
        $walidata = Auth::user();
        abort_unless($walidata && ($walidata->isWalidata() || $walidata->isSuperAdmin()), 403);
        if (! in_array($value->status, [DatasetValue::STATUS_SUBMITTED, DatasetValue::STATUS_VERIFIED], true)) {
            throw new LogicException('Hanya data submitted/verified yang dapat ditolak.');
        }

        $previous = $value->status;
        $value->status = DatasetValue::STATUS_REJECTED;
        $value->status_verifikasi = DatasetValue::STATUS_REJECTED;
        $value->verified_at = now();
        $value->verified_by = $walidata->id;
        $value->notes = $notes;
        $value->save();

        $this->logWorkflow($value, $previous, DatasetValue::STATUS_REJECTED, $notes, $walidata->id);
        $this->notifyDatasetStakeholders($value, 'rejected');
        return true;
    }

    protected function buildValue(Dataset $dataset, array $data): DatasetValue
    {
        $value = new DatasetValue();
        $value->dataset_id = $dataset->id;
        $value->tahun = $data['tahun'] ?? date('Y');
        $value->periode_label = $data['periode_label'] ?? null;
        $value->wilayah_type = $data['wilayah_type'] ?? 'kabupaten';
        $value->wilayah_id = $data['wilayah_id'] ?? null;

        // Beberapa dataset menyimpan kecamatan sebagai field referensi dinamis,
        // bukan pada metadata wilayah form. Ikat record ke poligon kecamatan juga.
        foreach ($dataset->fields as $field) {
            if ($field->reference_target === 'wilayahs' && str_contains(strtolower($field->nama), 'kecamatan')) {
                $districtId = data_get($data, 'values.' . $field->nama);
                if (is_numeric($districtId)) {
                    $value->wilayah_type = 'kecamatan';
                    $value->wilayah_id = (int) $districtId;
                    break;
                }
            }
        }
        $value->created_by = Auth::id();
        $this->applyData($value, $data);
        return $value;
    }

    private function normalizeInput(Dataset $dataset,array $data): array
    {
        $data['dataset_id']=$dataset->id;
        if(isset($data['tahun']) && $dataset->fields->contains('nama','tahun')) $data['values']['tahun']=(int)$data['tahun'];
        if((string)$dataset->kode==='5.59')$data['values']=RoadInputDefinition::normalize($data['values']??[]);
        return $data;
    }

    protected function applyData(DatasetValue $value, array $data): void
    {
        if (isset($data['tahun'])) $value->tahun = (int) $data['tahun'];
        if (isset($data['periode_label'])) $value->periode_label = $data['periode_label'];
        if (isset($data['wilayah_type'])) $value->wilayah_type = $data['wilayah_type'];
        if (isset($data['wilayah_id'])) $value->wilayah_id = $data['wilayah_id'];

        $values = $value->values ?? [];
        foreach ($value->dataset->fields as $field) {
            $key = 'values.' . $field->nama;
            if (array_key_exists($key, $data)) {
                $values[$field->nama] = $data[$key];
            } elseif (array_key_exists($field->nama, $data['values'] ?? [])) {
                $values[$field->nama] = $data['values'][$field->nama];
            }
        }
        if (! empty($values)) {
            // Auto-calculate derived fields
            $values = FieldAutoCalculator::apply($value->dataset, $values);
            $value->values = $values;
            foreach ($value->dataset->fields as $field) {
                if (in_array($field->reference_target,['wilayah','wilayahs'],true) && str_contains($field->nama,'kecamatan') && is_numeric($values[$field->nama]??null)) {
                    $value->wilayah_type='kecamatan'; $value->wilayah_id=(int)$values[$field->nama];
                }
            }
        }
    }

    protected function logWorkflow(DatasetValue $value, ?string $from, string $to, ?string $notes = null, ?int $actorId = null): void
    {
        try {
            DB::table('workflow_logs')->insert([
                'batch_id' => null,
                'data_value_id' => $value->id,
                'from_status' => $from,
                'to_status' => $to,
                'workflow_stage' => match ($to) {
                    DatasetValue::STATUS_SUBMITTED => 'collect',
                    DatasetValue::STATUS_VERIFIED => 'verify',
                    DatasetValue::STATUS_PUBLISHED => 'publish',
                    default => null,
                },
                'actor_id' => $actorId ?? Auth::id(),
                'notes' => $notes,
                'reason' => $notes,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to log workflow: ' . $e->getMessage());
        }
    }

    private function assertProducerAccess(Dataset $dataset): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isSuperAdmin() || $user->opd_id === $dataset->opd_id), 403);
    }

    private function notifyDatasetStakeholders(DatasetValue $value, string $status): void
    {
        $actor = Auth::user();
        $dataset = $value->dataset()->with('opd')->first();
        if (! $dataset) return;

        $recipients = User::query()
            ->when($actor, fn ($query) => $query->whereKeyNot($actor->id))
            ->where(function ($query) use ($dataset, $status) {
                if ($status === 'submitted') {
                    $query->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['admin_kominfo', 'walidata', 'admin', 'super_admin']));
                } else {
                    $query->where('opd_id', $dataset->opd_id);
                }
            })->get();

        if ($recipients->isEmpty()) return;

        $label = match ($status) {
            'submitted' => 'menunggu verifikasi Kominfo',
            'verified' => 'telah diverifikasi Kominfo',
            'rejected' => 'ditolak Kominfo',
            'revision' => 'memerlukan revisi',
            default => 'diperbarui',
        };
        $notification = new BatchNotification(
            'Data ' . $label,
            sprintf('Data pada dataset %s (%s) %s.%s', $dataset->nama, $dataset->kode, $label, $value->notes ? ' Catatan: ' . $value->notes : ''),
            'DatasetValue',
            $value->getKey(),
            $actor?->id ?? 0,
            $actor?->name ?? 'System'
        );
        Notification::sendNow($recipients, $notification);
    }

    private function notifyDatasetSubmission(DatasetSubmission $submission, User $actor): void
    {
        $recipients = User::query()->whereHas('roles', fn ($roles) => $roles->whereIn('name', [
            'admin_kominfo', 'walidata', 'admin', 'super_admin',
        ]))->get()->reject(fn (User $user) => (int) $user->id === (int) $actor->id);

        if ($recipients->isEmpty()) return;

        $notification = new BatchNotification(
            'Pengiriman data OPD menunggu verifikasi',
            sprintf('%s mengirim %d data sektoral (%s).', $actor->opd?->nama ?? 'OPD', $submission->values()->count(), $submission->notes ? 'Catatan: ' . $submission->notes : 'tanpa catatan'),
            'DatasetSubmission',
            $submission->id,
            $actor->id,
            $actor->name
        );
        Notification::sendNow($recipients, $notification);
    }
}
