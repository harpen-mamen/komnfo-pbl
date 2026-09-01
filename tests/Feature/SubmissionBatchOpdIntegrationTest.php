<?php

namespace Tests\Feature;

use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Services\BatchApprovalService;
use App\Services\SubmissionBatchService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tests for SPEC-003: OPD Batch Workflow Integration
 *
 * Before fix: DataKesehatanBulanan and DataPendidikanBulanan had no
 *            submission_batch_id FK, bypassing the entire approval workflow.
 *
 * After fix:  Every OPD record is linked to a submission batch via FK,
 *            the HasSubmissionBatchApproval trait works correctly,
 *            and records cannot be modified after batch approval.
 */
class SubmissionBatchOpdIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_kecamatan', 'admin_desa', 'admin_kominfo', 'admin', 'super_admin'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_kesehatan_data_is_linked_to_submission_batch(): void
    {
        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $user = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $user->assignRole('admin_kecamatan');

        // Create kesehatan data
        $kesehatan = DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        // Submit batch
        $batches = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_KESEHATAN], 5, null, 2026, 'test opd', $user);

        $batch = $batches->first();

        // Verify: every kesehatan record has submission_batch_id assigned
        $kesehatan->refresh();

        $this->assertEquals($batch->id, $kesehatan->submission_batch_id);
        $this->assertDatabaseHas('data_kesehatan_bulanans', [
            'id' => $kesehatan->id,
            'submission_batch_id' => $batch->id,
        ]);
    }

    public function test_pendidikan_data_is_linked_to_submission_batch(): void
    {
        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $user = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $user->assignRole('admin_kecamatan');

        // Create pendidikan data
        $pendidikan = DataPendidikanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'semester' => 'ganjil',
            'periode_tahun' => 2026,
        ]);

        // Submit batch
        $batches = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_PENDIDIKAN], null, 'ganjil', 2026, 'test opd', $user);

        $batch = $batches->first();

        // Verify: every pendidikan record has submission_batch_id assigned
        $pendidikan->refresh();

        $this->assertEquals($batch->id, $pendidikan->submission_batch_id);
        $this->assertDatabaseHas('data_pendidikan_bulanans', [
            'id' => $pendidikan->id,
            'submission_batch_id' => $batch->id,
        ]);
    }

    public function test_kesehatan_data_cannot_be_modified_after_approval(): void
    {
        Notification::fake();
        Mail::fake();

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $sender = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $sender->assignRole('admin_kecamatan');

        $approver = User::factory()->create();
        $approver->assignRole('admin');

        // Create kesehatan data
        $kesehatan = DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        // Submit and approve batch
        $batches = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_KESEHATAN], 5, null, 2026, 'test approval', $sender);

        $batch = $batches->first();

        app(BatchApprovalService::class)->startReview($batch, $approver);
        app(BatchApprovalService::class)->approve($batch->fresh(), $approver);

        // Try to modify kesehatan data → should fail
        $kesehatan->refresh();
        $kesehatan->stunting = 999;

        $this->expectException(AuthorizationException::class);
        $kesehatan->save();
    }

    public function test_pendidikan_data_cannot_be_modified_after_approval(): void
    {
        Notification::fake();
        Mail::fake();

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $sender = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $sender->assignRole('admin_kecamatan');

        $approver = User::factory()->create();
        $approver->assignRole('admin');

        // Create pendidikan data
        $pendidikan = DataPendidikanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'semester' => 'ganjil',
            'periode_tahun' => 2026,
        ]);

        // Submit and approve batch
        $batches = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_PENDIDIKAN], null, 'ganjil', 2026, 'test approval', $sender);

        $batch = $batches->first();

        app(BatchApprovalService::class)->startReview($batch, $approver);
        app(BatchApprovalService::class)->approve($batch->fresh(), $approver);

        // Try to modify pendidikan data → should fail
        $pendidikan->refresh();
        $pendidikan->jumlah_siswa = 9999;

        $this->expectException(AuthorizationException::class);
        $pendidikan->save();
    }

    public function test_mixed_batch_links_all_opd_records(): void
    {
        Notification::fake();

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $user = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $user->assignRole('admin_kecamatan');

        // Create both kesehatan and pendidikan data
        $kesehatan = DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        $pendidikan = DataPendidikanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'semester' => 'ganjil',
            'periode_tahun' => 2026,
        ]);

        // Submit mixed batch
        $batches = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [
                SubmissionBatch::MODULE_KESEHATAN,
                SubmissionBatch::MODULE_PENDIDIKAN,
            ], 5, 'ganjil', 2026, 'mixed opd test', $user);

        $batch = $batches->first();

        $kesehatan->refresh();
        $pendidikan->refresh();

        $this->assertEquals($batch->id, $kesehatan->submission_batch_id);
        $this->assertEquals($batch->id, $pendidikan->submission_batch_id);
        $this->assertEquals(SubmissionBatch::STATUS_PENDING, $batch->status);
    }

    public function test_batch_items_created_for_opd_data(): void
    {
        Notification::fake();

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $user = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $user->assignRole('admin_kecamatan');

        $kesehatan = DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        $batches = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_KESEHATAN], 5, null, 2026, 'test items', $user);

        $batch = $batches->first();

        // Verify batch item was created
        $this->assertDatabaseHas('submission_batch_items', [
            'submission_batch_id' => $batch->id,
            'entity_type' => DataKesehatanBulanan::class,
            'entity_id' => $kesehatan->id,
            'package_key' => SubmissionBatch::MODULE_KESEHATAN,
        ]);
    }
}