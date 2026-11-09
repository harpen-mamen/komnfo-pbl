<?php

namespace Tests\Feature;

use App\Filament\Resources\SubmissionBatchResource\Pages\ViewSubmissionBatch;
use App\Models\Wilayah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\MobileFieldPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubmissionBatchResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_kominfo_can_load_the_submission_batch_index(): void
    {
        Role::firstOrCreate(['name' => 'admin_kominfo', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('admin_kominfo');

        $this->actingAs($user)
            ->get('/admin/submission-batches')
            ->assertOk();
    }

    public function test_kecamatan_can_start_review_for_a_desa_batch(): void
    {
        Role::firstOrCreate(['name' => 'admin_desa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin_kecamatan', 'guard_name' => 'web']);

        $kabupaten = Wilayah::query()->create(['tipe' => Wilayah::TIPE_KABUPATEN, 'nama' => 'Kabupaten Resource']);
        $kecamatan = Wilayah::query()->create(['parent_id' => $kabupaten->id, 'tipe' => Wilayah::TIPE_KECAMATAN, 'nama' => 'Kecamatan Resource']);
        $desa = Wilayah::query()->create(['parent_id' => $kecamatan->id, 'tipe' => Wilayah::TIPE_DESA, 'nama' => 'Desa Resource']);

        $desaUser = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $desaUser->assignRole('admin_desa');

        $kecamatanUser = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $kecamatanUser->assignRole('admin_kecamatan');

        $batch = SubmissionBatch::create([
            'module' => SubmissionBatch::MODULE_LAPANGAN,
            'submission_number' => 'SB-REVIEW-KEC-001',
            'kecamatan_id' => $kecamatan->id,
            'status' => SubmissionBatch::STATUS_PENDING,
            'created_by' => $desaUser->id,
            'submitted_at' => now(),
            'package_keys' => [SubmissionBatch::MODULE_LAPANGAN],
            'package_signature' => SubmissionBatch::MODULE_LAPANGAN,
            'version' => 1,
        ]);
        $point = MobileFieldPoint::query()->create([
            'operation_id' => (string) \Illuminate\Support\Str::uuid(),
            'desa_id' => $desa->id,
            'jenis' => 'rumah',
            'nama' => 'Titik uji',
            'latitude' => '3.5000000',
            'longitude' => '125.5000000',
            'created_by' => $desaUser->id,
            'submission_batch_id' => $batch->id,
        ]);
        SubmissionBatchItem::query()->create([
            'submission_batch_id' => $batch->id,
            'package_key' => SubmissionBatch::MODULE_LAPANGAN,
            'entity_type' => MobileFieldPoint::class,
            'entity_id' => $point->id,
        ]);

        Livewire::actingAs($kecamatanUser)
            ->test(ViewSubmissionBatch::class, ['record' => $batch->id])
            ->assertOk();
        app(\App\Services\BatchApprovalService::class)->startReview($batch, $kecamatanUser);

        $this->assertSame(SubmissionBatch::STATUS_IN_REVIEW, $batch->fresh()->status);
    }
}
