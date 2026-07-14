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
}
