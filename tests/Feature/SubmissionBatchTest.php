<?php

namespace Tests\Feature;

use App\Models\MobileFieldPoint;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\BatchApprovalService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubmissionBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_kecamatan_reviews_and_forwards_mobile_point_before_kominfo_approval(): void
    {
        $this->roles();
        [$kecamatan, $desa] = $this->wilayah();
        $creator = $this->user('admin_desa', $kecamatan, $desa);
        $reviewer = $this->user('admin_kecamatan', $kecamatan);
        $kominfo = $this->user('admin_kominfo');
        $batch = $this->batchWithPoint($creator, $kecamatan, $desa);
        $service = app(BatchApprovalService::class);

        $service->startReview($batch, $reviewer);
        try {
            $service->approve($batch->fresh(), $kominfo);
            $this->fail('Kominfo tidak boleh menyetujui sebelum batch lapangan diteruskan kecamatan.');
        } catch (AuthorizationException) {
            $this->assertFalse($batch->fresh()->isForwarded());
        }

        $service->forwardToKominfo($batch->fresh(), $reviewer);
        $service->approve($batch->fresh(), $kominfo);
        $this->assertSame(SubmissionBatch::STATUS_APPROVED, $batch->fresh()->status);
        $this->assertTrue($batch->fresh()->isForwarded());
    }

    public function test_kecamatan_cannot_review_a_batch_from_another_kecamatan(): void
    {
        $this->roles();
        [$kecamatanA, $desaA] = $this->wilayah();
        [$kecamatanB, $desaB] = $this->wilayah();
        $creator = $this->user('admin_desa', $kecamatanA, $desaA);
        $reviewer = $this->user('admin_kecamatan', $kecamatanB);
        $batch = $this->batchWithPoint($creator, $kecamatanA, $desaA);

        $this->expectException(AuthorizationException::class);
        app(BatchApprovalService::class)->startReview($batch, $reviewer);
    }

    public function test_mobile_point_batch_revision_can_be_recorded_and_notified_to_creator(): void
    {
        $this->roles();
        \Illuminate\Support\Facades\Notification::fake();
        [$kecamatan, $desa] = $this->wilayah();
        $creator = $this->user('admin_desa', $kecamatan, $desa);
        $reviewer = $this->user('admin_kecamatan', $kecamatan);
        $batch = $this->batchWithPoint($creator, $kecamatan, $desa);
        $service = app(BatchApprovalService::class);
        $service->startReview($batch, $reviewer);
        $service->requestRevision($batch->fresh(), $reviewer, 'Periksa ulang koordinat.');

        $this->assertSame(SubmissionBatch::STATUS_REVISION, $batch->fresh()->status);
        \Illuminate\Support\Facades\Notification::assertSentTo($creator, \App\Notifications\BatchNotification::class);
    }

    private function batchWithPoint(User $creator, Wilayah $kecamatan, Wilayah $desa): SubmissionBatch
    {
        $batch = SubmissionBatch::query()->create([
            'module' => SubmissionBatch::MODULE_LAPANGAN,
            'submission_number' => 'SB-TEST-'.Str::upper(Str::random(12)),
            'kecamatan_id' => $kecamatan->id,
            'status' => SubmissionBatch::STATUS_PENDING,
            'created_by' => $creator->id,
            'submitted_at' => now(),
            'package_keys' => [SubmissionBatch::MODULE_LAPANGAN],
            'package_signature' => hash('sha256', (string) Str::uuid()),
            'version' => 1,
            'payload_snapshot' => ['source' => 'mobile', 'module' => SubmissionBatch::MODULE_LAPANGAN],
        ]);
        $point = MobileFieldPoint::query()->create([
            'operation_id' => (string) Str::uuid(), 'desa_id' => $desa->id,
            'jenis' => 'rumah', 'nama' => 'Titik uji', 'latitude' => '3.5000000',
            'longitude' => '125.5000000', 'created_by' => $creator->id,
            'submission_batch_id' => $batch->id, 'status' => SubmissionBatch::STATUS_PENDING,
        ]);
        SubmissionBatchItem::query()->create([
            'submission_batch_id' => $batch->id, 'package_key' => SubmissionBatch::MODULE_LAPANGAN,
            'entity_type' => MobileFieldPoint::class, 'entity_id' => $point->id,
        ]);
        return $batch;
    }

    private function wilayah(): array
    {
        $kabupaten = Wilayah::query()->create(['tipe' => Wilayah::TIPE_KABUPATEN, 'nama' => 'Kabupaten Uji '.Str::random(4)]);
        $kecamatan = Wilayah::query()->create(['parent_id' => $kabupaten->id, 'tipe' => Wilayah::TIPE_KECAMATAN, 'nama' => 'Kecamatan Uji']);
        $desa = Wilayah::query()->create(['parent_id' => $kecamatan->id, 'tipe' => Wilayah::TIPE_DESA, 'nama' => 'Desa Uji']);
        return [$kecamatan, $desa];
    }

    private function user(string $role, ?Wilayah $kecamatan = null, ?Wilayah $desa = null): User
    {
        $user = User::factory()->create(['kecamatan_id' => $kecamatan?->id, 'desa_id' => $desa?->id]);
        $user->assignRole($role);
        return $user;
    }

    private function roles(): void
    {
        foreach (['admin_desa', 'admin_kecamatan', 'admin_kominfo'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
