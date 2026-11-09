<?php

namespace Tests\Feature;

use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Wilayah;
use App\Notifications\BatchNotification;
use App\Services\BatchApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubmissionBatchNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_batch_submission_notifies_the_responsible_kecamatan(): void
    {
        Notification::fake();
        [$kecamatan, $desa] = $this->wilayah();
        $creator = $this->user('admin_desa', $kecamatan, $desa);
        $reviewer = $this->user('admin_kecamatan', $kecamatan);
        $batch = $this->batch($creator, $kecamatan);

        app(BatchApprovalService::class)->submit($batch, $creator);

        Notification::assertSentTo($reviewer, BatchNotification::class);
    }

    public function test_forwarding_mobile_batch_notifies_kominfo(): void
    {
        Notification::fake();
        [$kecamatan, $desa] = $this->wilayah();
        $creator = $this->user('admin_desa', $kecamatan, $desa);
        $reviewer = $this->user('admin_kecamatan', $kecamatan);
        $kominfo = $this->user('admin_kominfo');
        $batch = $this->batch($creator, $kecamatan);
        $service = app(BatchApprovalService::class);
        $service->submit($batch, $creator);
        $batch = $batch->fresh();
        $service->startReview($batch, $reviewer);
        $service->forwardToKominfo($batch->fresh(), $reviewer);

        Notification::assertSentTo($kominfo, BatchNotification::class);
    }

    private function wilayah(): array
    {
        $kabupaten = Wilayah::query()->create(['tipe' => Wilayah::TIPE_KABUPATEN, 'nama' => 'Kabupaten Notif '.Str::random(4)]);
        $kecamatan = Wilayah::query()->create(['parent_id' => $kabupaten->id, 'tipe' => Wilayah::TIPE_KECAMATAN, 'nama' => 'Kecamatan Notif']);
        $desa = Wilayah::query()->create(['parent_id' => $kecamatan->id, 'tipe' => Wilayah::TIPE_DESA, 'nama' => 'Desa Notif']);
        return [$kecamatan, $desa];
    }

    private function user(string $role, ?Wilayah $kecamatan = null, ?Wilayah $desa = null): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create(['kecamatan_id' => $kecamatan?->id, 'desa_id' => $desa?->id]);
        $user->assignRole($role);
        return $user;
    }

    private function batch(User $creator, Wilayah $kecamatan): SubmissionBatch
    {
        return SubmissionBatch::query()->create([
            'module' => SubmissionBatch::MODULE_LAPANGAN,
            'submission_number' => 'SB-NOTIF-'.Str::upper(Str::random(12)),
            'kecamatan_id' => $kecamatan->id,
            'status' => SubmissionBatch::STATUS_DRAFT,
            'created_by' => $creator->id,
            'package_keys' => [SubmissionBatch::MODULE_LAPANGAN],
            'package_signature' => hash('sha256', (string) Str::uuid()),
            'version' => 1,
            'payload_snapshot' => ['source' => 'mobile', 'module' => SubmissionBatch::MODULE_LAPANGAN],
        ]);
    }
}
