<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Filament\Resources\SubmissionBatchResource\Pages\ViewSubmissionBatch;
use App\Services\SubmissionBatchService;
use App\Services\BatchApprovalService;
use App\Services\SubmissionPublishService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_desa_can_only_submit_records_from_assigned_desa(): void
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_desa']);
        $kecamatan = Kecamatan::factory()->create();
        $desaA = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $desaB = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $user = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desaA->id,
        ]);
        $user->assignRole($role);

        $rumahA = Rumah::factory()->create(['desa_id' => $desaA->id]);
        $rumahB = Rumah::factory()->create(['desa_id' => $desaB->id]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, null, $user)
            ->first()
            ->fresh(['items']);

        $this->assertSame([$rumahA->id], $batch->items->pluck('entity_id')->all());
        $this->assertFalse($batch->items->pluck('entity_id')->contains($rumahB->id));
    }

    public function test_kominfo_cannot_decide_desa_batch_before_kecamatan_forwards_it(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_desa']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kominfo']);

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $desaUser = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $desaUser->assignRole('admin_desa');

        $kecamatanUser = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $kecamatanUser->assignRole('admin_kecamatan');

        $kominfoUser = User::factory()->create();
        $kominfoUser->assignRole('admin_kominfo');

        Rumah::factory()->create(['desa_id' => $desa->id]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, null, $desaUser)
            ->first();

        app(BatchApprovalService::class)->startReview($batch, $kecamatanUser);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('harus direview dan diteruskan oleh Admin Kecamatan');

        app(BatchApprovalService::class)->approve($batch->fresh(), $kominfoUser);
    }

    public function test_kecamatan_can_request_revision_from_desa_before_forwarding(): void
    {
        Notification::fake();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_desa']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $desaUser = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $desaUser->assignRole('admin_desa');

        $kecamatanUser = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $kecamatanUser->assignRole('admin_kecamatan');

        Rumah::factory()->create(['desa_id' => $desa->id]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, null, $desaUser)
            ->first();

        app(BatchApprovalService::class)->startReview($batch, $kecamatanUser);
        app(BatchApprovalService::class)->requestRevision($batch->fresh(), $kecamatanUser, 'Lengkapi alamat rumah.');

        $this->assertSame(SubmissionBatch::STATUS_REVISION, $batch->fresh()->status);
        Notification::assertSentTo($desaUser, \App\Notifications\BatchNotification::class);
    }

    public function test_kominfo_can_decide_desa_batch_after_kecamatan_forwards_it(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_desa']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kominfo']);

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $desaUser = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $desaUser->assignRole('admin_desa');

        $kecamatanUser = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $kecamatanUser->assignRole('admin_kecamatan');

        $kominfoUser = User::factory()->create();
        $kominfoUser->assignRole('admin_kominfo');

        Rumah::factory()->create(['desa_id' => $desa->id]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, null, $desaUser)
            ->first();

        app(BatchApprovalService::class)->startReview($batch, $kecamatanUser);
        app(BatchApprovalService::class)->forwardToKominfo($batch->fresh(), $kecamatanUser);
        app(BatchApprovalService::class)->approve($batch->fresh(), $kominfoUser);

        $this->assertSame(SubmissionBatch::STATUS_APPROVED, $batch->fresh()->status);
    }

    public function test_prevent_empty_submission_batch(): void
    {
        $user = User::factory()->create();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        $user->assignRole('admin_kecamatan');

        $kecamatan = Kecamatan::factory()->create();
        $user->kecamatan_id = $kecamatan->id;
        $user->save();

        $this->actingAs($user);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pilih minimal satu modul data untuk dikirim.');

        app(SubmissionBatchService::class)->submit($kecamatan->id, [], null, null, null, 'test empty', $user);
    }

    public function test_payload_snapshot_is_saved_on_batch_creation(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        $user->assignRole('admin_kecamatan');

        $kecamatan = Kecamatan::factory()->create();
        $user->kecamatan_id = $kecamatan->id;
        $user->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        Rumah::factory()->create(['desa_id' => $desa->id]);

        /** @var Collection<int, SubmissionBatch> $batches */
        $batches = app(SubmissionBatchService::class)->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'test snapshot', $user);
        $batch = $batches->first();

        $this->assertInstanceOf(SubmissionBatch::class, $batch);
        $this->assertSame(SubmissionBatch::MODULE_RUMAH, $batch->module);
        $this->assertSame(SubmissionBatch::STATUS_PENDING, $batch->status);
        $this->assertNotNull($batch->payload_snapshot);
        $this->assertEquals(2, $batch->version);
        $this->assertSame($kecamatan->id, $batch->kecamatan_id);
        $this->assertSame(SubmissionBatch::MODULE_RUMAH, $batch->payload_snapshot['module']);
        $this->assertSame('Rumah', $batch->payload_snapshot['module_label']);
        Mail::assertNothingSent();
    }

    public function test_batch_approval_marks_module_batch_as_public(): void
    {
        $sender = User::factory()->create();
        $approver = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $approver->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        Rumah::factory()->create(['desa_id' => $desa->id]);

        /** @var Collection<int, SubmissionBatch> $batches */
        $batches = app(SubmissionBatchService::class)->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'sync test', $sender);
        $batch = $batches->first();

        app(BatchApprovalService::class)->startReview($batch, $approver);
        app(BatchApprovalService::class)->approve($batch, $approver);

        $this->assertSame(SubmissionBatch::STATUS_APPROVED, $batch->fresh()->status);
        $this->assertSame(1, Rumah::query()->inApprovedBatch()->count());
    }

    public function test_mixed_submission_batch_contains_all_selected_modules_in_one_submission(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        $user->assignRole('admin_kecamatan');

        $kecamatan = Kecamatan::factory()->create();
        $user->kecamatan_id = $kecamatan->id;
        $user->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $rumah = Rumah::factory()->create(['desa_id' => $desa->id]);
        $keluarga = Keluarga::factory()->create(['rumah_id' => $rumah->id]);
        Penduduk::factory()->create([
            'keluarga_id' => $keluarga->id,
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-01-01',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'status_dalam_keluarga' => 'Anak',
            'status_penduduk' => 'Tetap',
        ]);
        DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);
        DataPendidikanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'semester' => 'ganjil',
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        $batches = app(SubmissionBatchService::class)->submit($kecamatan->id, [
            SubmissionBatch::MODULE_RUMAH,
            SubmissionBatch::MODULE_KELUARGA,
            SubmissionBatch::MODULE_PENDUDUK,
            SubmissionBatch::MODULE_KESEHATAN,
            SubmissionBatch::MODULE_PENDIDIKAN,
        ], 5, 'ganjil', 2026, 'mixed test', $user);

        $this->assertCount(1, $batches);

        /** @var SubmissionBatch $batch */
        $batch = $batches->first()->fresh(['items']);

        $this->assertSame(SubmissionBatch::MODULE_MIXED, $batch->module);
        $this->assertSame(SubmissionBatch::STATUS_PENDING, $batch->status);
        $this->assertEqualsCanonicalizing([
            SubmissionBatch::MODULE_RUMAH,
            SubmissionBatch::MODULE_KELUARGA,
            SubmissionBatch::MODULE_PENDUDUK,
            SubmissionBatch::MODULE_KESEHATAN,
            SubmissionBatch::MODULE_PENDIDIKAN,
        ], $batch->package_keys);
        $this->assertSame(5, $batch->items()->count());
        $this->assertEqualsCanonicalizing($batch->package_keys, $batch->items->pluck('package_key')->all());
        $this->assertSame(5, $batch->payload_snapshot['total_count']);
        $this->assertCount(5, $batch->payload_snapshot['modules']);
        $this->assertSame(1, $batch->payload_snapshot['modules'][0]['count']);

        Rumah::factory()->create(['desa_id' => $desa->id]);

        $this->assertSame(5, $batch->fresh()->payload_snapshot['total_count']);
        Mail::assertNothingSent();
    }

    public function test_kominfo_can_review_mixed_batch_with_workflow_history_and_comments(): void
    {
        Mail::fake();
        Notification::fake();

        $sender = User::factory()->create();
        $approver = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $approver->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        Rumah::factory()->create(['desa_id' => $desa->id]);
        DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [
                SubmissionBatch::MODULE_RUMAH,
                SubmissionBatch::MODULE_KESEHATAN,
            ], 5, null, 2026, 'review test', $sender)
            ->first();

        app(BatchApprovalService::class)->startReview($batch, $approver, 'Mulai verifikasi Kominfo');
        app(BatchApprovalService::class)->approve($batch->fresh(), $approver, 'Data valid untuk publikasi');

        $workflowInstanceId = DB::table('workflow_instances')
            ->where('submission_batch_id', $batch->id)
            ->value('id');

        $this->assertNotNull($workflowInstanceId);
        $this->assertDatabaseHas('workflow_histories', [
            'workflow_instance_id' => $workflowInstanceId,
            'catatan' => 'Mulai verifikasi Kominfo',
        ]);
        $this->assertDatabaseHas('workflow_histories', [
            'workflow_instance_id' => $workflowInstanceId,
            'catatan' => 'Data valid untuk publikasi',
        ]);
        $this->assertDatabaseHas('verification_comments', [
            'workflow_instance_id' => $workflowInstanceId,
            'komentar' => 'Data valid untuk publikasi',
            'tipe' => SubmissionBatch::STATUS_APPROVED,
        ]);

        Livewire::actingAs($approver)
            ->test(ViewSubmissionBatch::class, ['record' => $batch->id])
            ->assertSee('Isi Batch')
            ->assertSee('Rumah')
            ->assertSee('Kesehatan')
            ->assertSee('Snapshot Pengiriman')
            ->assertSee('Riwayat Verifikasi')
            ->assertSee('Komentar Verifikasi')
            ->assertSee('Data valid untuk publikasi');
    }

    public function test_approved_mixed_batch_publishes_snapshot_payload_idempotently(): void
    {
        Mail::fake();
        Notification::fake();

        $sender = User::factory()->create();
        $approver = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $approver->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $rumah = Rumah::factory()->create(['desa_id' => $desa->id, 'alamat_lengkap' => 'Alamat snapshot']);
        $keluarga = Keluarga::factory()->create(['rumah_id' => $rumah->id]);
        Penduduk::factory()->create([
            'keluarga_id' => $keluarga->id,
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-01-01',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'status_dalam_keluarga' => 'Anak',
            'status_penduduk' => 'Tetap',
        ]);
        DataKesehatanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);
        DataPendidikanBulanan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
            'semester' => 'ganjil',
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

        $batch = app(SubmissionBatchService::class)->submit($kecamatan->id, [
            SubmissionBatch::MODULE_RUMAH,
            SubmissionBatch::MODULE_KELUARGA,
            SubmissionBatch::MODULE_PENDUDUK,
            SubmissionBatch::MODULE_KESEHATAN,
            SubmissionBatch::MODULE_PENDIDIKAN,
        ], 5, 'ganjil', 2026, 'publish test', $sender)->first();

        $rumah->update(['alamat_lengkap' => 'Alamat mutable berubah']);

        app(BatchApprovalService::class)->startReview($batch, $approver);
        app(BatchApprovalService::class)->approve($batch->fresh(), $approver, 'Publish final');

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => SubmissionBatch::class,
            'entity_id' => $batch->id,
            'field' => 'submission_publish',
            'aksi' => 'publish_batch',
        ]);
        $this->assertSame(5, DB::table('audit_trails')->where('aksi', 'publish_record')->count());

        $publishedRumah = DB::table('audit_trails')
            ->where('entity_type', Rumah::class)
            ->where('entity_id', $rumah->id)
            ->where('field', 'published_payload')
            ->first();

        $payload = json_decode($publishedRumah->nilai_baru, true);
        $this->assertSame('Alamat snapshot', $payload['payload']['alamat_lengkap']);

        app(SubmissionPublishService::class)->publish($batch->fresh(), $approver);

        $this->assertSame(1, DB::table('audit_trails')->where('aksi', 'publish_batch')->count());
        $this->assertSame(5, DB::table('audit_trails')->where('aksi', 'publish_record')->count());
    }

    public function test_publish_failure_rolls_back_approval(): void
    {
        Mail::fake();
        Notification::fake();

        $sender = User::factory()->create();
        $approver = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $approver->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        Rumah::factory()->create(['desa_id' => $desa->id]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'rollback test', $sender)
            ->first();

        app(BatchApprovalService::class)->startReview($batch, $approver);

        $batch = $batch->fresh();
        $snapshot = $batch->payload_snapshot;
        unset($snapshot['records']);
        $batch->forceFill(['payload_snapshot' => $snapshot])->saveQuietly();

        $this->expectException(\RuntimeException::class);

        try {
            app(BatchApprovalService::class)->approve($batch->fresh(), $approver, 'Should rollback');
        } finally {
            $this->assertSame(SubmissionBatch::STATUS_IN_REVIEW, $batch->fresh()->status);
            $this->assertSame(0, DB::table('audit_trails')->where('aksi', 'publish_batch')->count());
            $this->assertSame(0, DB::table('audit_trails')->where('aksi', 'publish_record')->count());
        }
    }

    public function test_reject_stores_reason_reviewer_and_timestamp(): void
    {
        Mail::fake();
        Notification::fake();

        $sender = User::factory()->create();
        $reviewer = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $reviewer->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        Rumah::factory()->create(['desa_id' => $desa->id]);

        $batch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'reject test', $sender)
            ->first();

        app(BatchApprovalService::class)->startReview($batch, $reviewer);
        app(BatchApprovalService::class)->reject($batch->fresh(), $reviewer, 'Alamat belum lengkap');

        $batch = $batch->fresh();
        $workflowInstanceId = DB::table('workflow_instances')->where('submission_batch_id', $batch->id)->value('id');

        $this->assertSame(SubmissionBatch::STATUS_REJECTED, $batch->status);
        $this->assertSame($reviewer->id, $batch->verified_by);
        $this->assertNotNull($batch->verified_at);
        $this->assertStringContainsString('Alamat belum lengkap', $batch->notes);
        $this->assertDatabaseHas('workflow_histories', [
            'workflow_instance_id' => $workflowInstanceId,
            'alasan' => 'Alamat belum lengkap',
            'dilakukan_oleh' => $reviewer->id,
        ]);
        $this->assertDatabaseHas('verification_comments', [
            'workflow_instance_id' => $workflowInstanceId,
            'komentar' => 'Alamat belum lengkap',
            'tipe' => SubmissionBatch::STATUS_REJECTED,
        ]);
    }

    public function test_revise_rejected_batch_creates_new_snapshot_without_overwriting_previous_submission(): void
    {
        Mail::fake();
        Notification::fake();

        $sender = User::factory()->create();
        $reviewer = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $reviewer->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $rumah = Rumah::factory()->create(['desa_id' => $desa->id, 'alamat_lengkap' => 'Alamat versi pertama']);

        $firstBatch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'first', $sender)
            ->first();

        app(BatchApprovalService::class)->startReview($firstBatch, $reviewer);
        app(BatchApprovalService::class)->reject($firstBatch->fresh(), $reviewer, 'Perbaiki alamat');

        $rumah->update(['alamat_lengkap' => 'Alamat versi revisi']);

        $secondBatch = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'revision', $sender)
            ->first();

        $firstSnapshot = $firstBatch->fresh()->payload_snapshot;
        $secondSnapshot = $secondBatch->fresh()->payload_snapshot;

        $this->assertNotSame($firstBatch->id, $secondBatch->id);
        $this->assertSame(SubmissionBatch::STATUS_REJECTED, $firstBatch->fresh()->status);
        $this->assertSame(SubmissionBatch::STATUS_PENDING, $secondBatch->fresh()->status);
        $this->assertSame('Alamat versi pertama', $firstSnapshot['records'][0]['attributes']['alamat_lengkap']);
        $this->assertSame('Alamat versi revisi', $secondSnapshot['records'][0]['attributes']['alamat_lengkap']);
        $this->assertSame(1, $firstSnapshot['snapshot_version']);
        $this->assertSame(2, $secondSnapshot['snapshot_version']);
        $this->assertSame($firstBatch->id, $secondSnapshot['revision']['previous_submission_batch_id']);
    }

    public function test_multiple_revisions_preserve_history_and_select_latest_active_version(): void
    {
        Mail::fake();
        Notification::fake();

        $sender = User::factory()->create();
        $reviewer = User::factory()->create();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_kecamatan']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $sender->assignRole('admin_kecamatan');
        $reviewer->assignRole('admin');

        $kecamatan = Kecamatan::factory()->create();
        $sender->kecamatan_id = $kecamatan->id;
        $sender->save();

        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $rumah = Rumah::factory()->create(['desa_id' => $desa->id, 'alamat_lengkap' => 'Versi 1']);

        $batchOne = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'v1', $sender)
            ->first();
        app(BatchApprovalService::class)->startReview($batchOne, $reviewer, 'Review v1');
        app(BatchApprovalService::class)->reject($batchOne->fresh(), $reviewer, 'Tolak v1');

        $rumah->update(['alamat_lengkap' => 'Versi 2']);
        $batchTwo = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'v2', $sender)
            ->first();
        app(BatchApprovalService::class)->startReview($batchTwo, $reviewer, 'Review v2');
        app(BatchApprovalService::class)->reject($batchTwo->fresh(), $reviewer, 'Tolak v2');

        $rumah->update(['alamat_lengkap' => 'Versi 3']);
        $batchThree = app(SubmissionBatchService::class)
            ->submit($kecamatan->id, [SubmissionBatch::MODULE_RUMAH], null, null, null, 'v3', $sender)
            ->first();

        $this->assertSame(1, $batchOne->fresh()->payload_snapshot['snapshot_version']);
        $this->assertSame(2, $batchTwo->fresh()->payload_snapshot['snapshot_version']);
        $this->assertSame(3, $batchThree->fresh()->payload_snapshot['snapshot_version']);
        $this->assertSame('Versi 1', $batchOne->fresh()->payload_snapshot['records'][0]['attributes']['alamat_lengkap']);
        $this->assertSame('Versi 2', $batchTwo->fresh()->payload_snapshot['records'][0]['attributes']['alamat_lengkap']);
        $this->assertSame('Versi 3', $batchThree->fresh()->payload_snapshot['records'][0]['attributes']['alamat_lengkap']);
        $this->assertSame(7, DB::table('workflow_histories')->count());

        Livewire::actingAs($reviewer)
            ->test(ViewSubmissionBatch::class, ['record' => $batchThree->id])
            ->assertSee('Riwayat Versi')
            ->assertSee('Versi 1')
            ->assertSee('Versi 2')
            ->assertSee('Versi 3')
            ->assertSee('Tolak v1')
            ->assertSee('Tolak v2')
            ->assertSee('Versi aktif');
    }
}
