<?php

namespace Tests\Feature;

use App\Models\MobileFieldPoint;
use App\Models\SubmissionBatch;
use App\Models\Wilayah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MobileSyncNikUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_point_push_creates_a_pending_kecamatan_batch(): void
    {
        $this->seedRoles();
        [$kecamatan, $desa] = $this->wilayah();
        $user = User::factory()->create(['kecamatan_id' => $kecamatan->id, 'desa_id' => $desa->id]);
        $user->assignRole('admin_desa');
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/field-points', [
            'operations' => [$this->operation($desa->id)],
        ])->assertCreated()->assertJsonPath('results.0.status', 'pending');

        $point = MobileFieldPoint::query()->firstOrFail();
        $this->assertSame($response->json('results.0.batch_id'), $point->submission_batch_id);
        $this->assertDatabaseHas('submission_batches', [
            'id' => $point->submission_batch_id,
            'module' => SubmissionBatch::MODULE_LAPANGAN,
            'kecamatan_id' => $kecamatan->id,
            'status' => SubmissionBatch::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('submission_batch_items', [
            'submission_batch_id' => $point->submission_batch_id,
            'entity_type' => MobileFieldPoint::class,
            'entity_id' => $point->id,
        ]);
    }

    public function test_mobile_point_push_is_idempotent_for_same_operation(): void
    {
        $this->seedRoles();
        [$kecamatan, $desa] = $this->wilayah();
        $user = User::factory()->create(['kecamatan_id' => $kecamatan->id, 'desa_id' => $desa->id]);
        $user->assignRole('admin_desa');
        $operation = $this->operation($desa->id);

        $first = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/field-points', ['operations' => [$operation]])->assertCreated();
        $second = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/field-points', ['operations' => [$operation]])->assertCreated();
        $this->assertSame($first->json('results.0.server_id'), $second->json('results.0.server_id'));
        $this->assertSame(1, MobileFieldPoint::query()->count());
        $this->assertSame(1, SubmissionBatch::query()->count());
    }

    public function test_mobile_point_push_rejects_a_desa_outside_the_users_scope(): void
    {
        $this->seedRoles();
        [$kecamatanA, $desaA] = $this->wilayah();
        [, $desaB] = $this->wilayah();
        $user = User::factory()->create(['kecamatan_id' => $kecamatanA->id, 'desa_id' => $desaA->id]);
        $user->assignRole('admin_desa');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/mobile/field-points', ['operations' => [$this->operation($desaB->id)]])
            ->assertForbidden();
        $this->assertSame(0, MobileFieldPoint::query()->count());
    }

    private function seedRoles(): void
    {
        Role::firstOrCreate(['name' => 'admin_desa', 'guard_name' => 'web']);
    }

    private function wilayah(): array
    {
        $kabupaten = Wilayah::query()->create(['tipe' => Wilayah::TIPE_KABUPATEN, 'nama' => 'Kabupaten Uji']);
        $kecamatan = Wilayah::query()->create(['parent_id' => $kabupaten->id, 'tipe' => Wilayah::TIPE_KECAMATAN, 'nama' => 'Kecamatan Uji']);
        $desa = Wilayah::query()->create(['parent_id' => $kecamatan->id, 'tipe' => Wilayah::TIPE_DESA, 'nama' => 'Desa Uji']);
        return [$kecamatan, $desa];
    }

    private function operation(int $desaId): array
    {
        return [
            'operation_id' => (string) Str::uuid(),
            'desa_id' => $desaId,
            'jenis' => 'rumah',
            'nama' => 'Titik Uji',
            'latitude' => 3.5,
            'longitude' => 125.5,
        ];
    }
}
