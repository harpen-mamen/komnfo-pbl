<?php

namespace Tests\Feature;

use App\Filament\Resources\SubmissionBatchResource\Pages\ViewSubmissionBatch;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
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

        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $desaUser = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $desaUser->assignRole('admin_desa');

        $kecamatanUser = User::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $kecamatanUser->assignRole('admin_kecamatan');

        $batch = SubmissionBatch::create([
            'module' => SubmissionBatch::MODULE_RUMAH,
            'submission_number' => 'SB-REVIEW-KEC-001',
            'kecamatan_id' => $kecamatan->id,
            'status' => SubmissionBatch::STATUS_PENDING,
            'created_by' => $desaUser->id,
            'submitted_at' => now(),
            'package_keys' => [SubmissionBatch::MODULE_RUMAH],
            'package_signature' => SubmissionBatch::MODULE_RUMAH,
            'version' => 1,
        ]);

        Livewire::actingAs($kecamatanUser)
            ->test(ViewSubmissionBatch::class, ['record' => $batch->id])
            ->assertPageActionVisible('startReview')
            ->assertPageActionEnabled('startReview')
            ->callPageAction('startReview')
            ->assertPageActionVisible('forwardToKominfo');

        $this->assertSame(SubmissionBatch::STATUS_IN_REVIEW, $batch->fresh()->status);
    }
}
