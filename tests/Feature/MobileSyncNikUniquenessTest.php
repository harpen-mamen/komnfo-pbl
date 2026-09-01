<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests for SPEC-002: NIK Uniqueness Validation
 *
 * Before fix: Mobile sync allowed duplicate NIK because validation only
 *             checked ['required', 'string', 'max:32'] — no uniqueness check.
 *
 * After fix:  storePenduduk() checks NIK doesn't already exist in the
 *             same kecamatan before creating.
 */
class MobileSyncNikUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_desa', 'guard_name' => 'web']);
    }

    public function test_mobile_sync_accepts_new_nik(): void
    {
        $user = $this->setupUser();

        $rumahLocalId = 'local-rumah-'.Str::random(6);
        $keluargaLocalId = 'local-keluarga-'.Str::random(6);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/mobile/sync/push', [
                'device_id' => 'test-device',
                'operations' => [
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'rumah',
                        'local_id' => $rumahLocalId,
                        'payload' => [
                            'desa_id' => $user->desa_id,
                            'kode_rumah' => 'R-TEST-'.Str::random(6),
                            'alamat_lengkap' => 'Alamat Test',
                            'latitude' => 3.5,
                            'longitude' => 125.5,
                            'status_rumah' => 'Milik Sendiri',
                        ],
                    ],
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'keluarga',
                        'local_id' => $keluargaLocalId,
                        'payload' => [
                            'rumah_local_id' => $rumahLocalId,
                            'nomor_kk' => '3301'.Str::random(12),
                            'nama_kepala_keluarga' => 'Test Kepala Keluarga',
                            'jumlah_anggota' => 1,
                            'status_ekonomi' => 'Menengah',
                        ],
                    ],
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'penduduk',
                        'local_id' => 'local-1',
                        'payload' => [
                            'keluarga_local_id' => $keluargaLocalId,
                            'nik' => '3210987654321098',
                            'nama_lengkap' => 'Test Penduduk',
                            'jenis_kelamin' => 'Laki-laki',
                            'tempat_lahir' => 'Tahuna',
                            'tanggal_lahir' => '1990-01-15',
                            'agama' => 'Kristen',
                            'status_perkawinan' => 'Belum Kawin',
                            'pendidikan' => 'S1',
                            'pekerjaan' => 'Guru',
                            'status_dalam_keluarga' => 'Anak',
                            'status_penduduk' => 'Tetap',
                        ],
                    ],
                ],
            ]);

        $response->assertJsonPath('results.2.status', 'accepted');
        $this->assertDatabaseHas('penduduk', ['nik' => '3210987654321098']);
    }

    public function test_mobile_sync_rejects_duplicate_nik_in_same_kecamatan(): void
    {
        $user = $this->setupUser();
        $userKeluarga = $this->keluargaForUser($user);

        // Create existing penduduk with NIK 1234567890123456 (in user's scope)
        Penduduk::factory()->create(['nik' => '1234567890123456', 'keluarga_id' => $userKeluarga->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/mobile/sync/push', [
                'device_id' => 'test-device',
                'operations' => [
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'penduduk',
                        'local_id' => 'local-2',
                        'payload' => $this->validPendudukPayload('1234567890123456', $userKeluarga->id),
                    ],
                ],
            ]);

        $response->assertJsonPath('results.0.status', 'failed');
        $this->assertStringContainsString('NIK sudah terdaftar', $response->json('results.0.message'));
    }

    public function test_mobile_sync_allows_same_nik_in_different_kecamatan(): void
    {
        $userA = $this->setupUser();
        $kecamatanB = Kecamatan::factory()->create();
        $desaB = Desa::factory()->create(['kecamatan_id' => $kecamatanB->id]);
        $rumahB = Rumah::factory()->create(['desa_id' => $desaB->id]);
        $userB = User::factory()->create([
            'kecamatan_id' => $kecamatanB->id,
            'desa_id' => $desaB->id,
        ]);
        $userB->assignRole('admin_desa');

        // Create penduduk with NIK in kecamatan A
        $rumahA = Rumah::factory()->create(['desa_id' => $userA->desa_id]);
        $keluargaA = \App\Models\Keluarga::factory()->create(['rumah_id' => $rumahA->id]);
        Penduduk::factory()->create(['nik' => '9999888877776666', 'keluarga_id' => $keluargaA->id]);

        // Same NIK should be allowed in kecamatan B
        $keluargaB = \App\Models\Keluarga::factory()->create(['rumah_id' => $rumahB->id]);

        $response = $this->actingAs($userB, 'sanctum')
            ->postJson('/api/v1/mobile/sync/push', [
                'device_id' => 'test-device',
                'operations' => [
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'penduduk',
                        'local_id' => 'local-3',
                        'payload' => $this->validPendudukPayload('9999888877776666', $keluargaB->id),
                    ],
                ],
            ]);

        $response->assertJsonPath('results.0.status', 'accepted');
    }

    public function test_mobile_sync_reject_on_idempotent_retry_with_existing_nik(): void
    {
        $user = $this->setupUser();

        $rumahLocalId = 'local-rumah-idempotent';
        $keluargaLocalId = 'local-keluarga-idempotent';
        $opId = Str::uuid()->toString();

        // First submission with rumah+keluarga+penduduk chain (user scope)
        $response1 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/mobile/sync/push', [
                'device_id' => 'test-device',
                'operations' => [
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'rumah',
                        'local_id' => $rumahLocalId,
                        'payload' => [
                            'desa_id' => $user->desa_id,
                            'kode_rumah' => 'R-IDEM-'.Str::random(6),
                            'alamat_lengkap' => 'Alamat Idempoten',
                            'latitude' => 3.5,
                            'longitude' => 125.5,
                            'status_rumah' => 'Milik Sendiri',
                        ],
                    ],
                    [
                        'operation_id' => Str::uuid()->toString(),
                        'dataset' => 'keluarga',
                        'local_id' => $keluargaLocalId,
                        'payload' => [
                            'rumah_local_id' => $rumahLocalId,
                            'nomor_kk' => '3301'.Str::random(12),
                            'nama_kepala_keluarga' => 'Test Kepala',
                            'jumlah_anggota' => 1,
                            'status_ekonomi' => 'Menengah',
                        ],
                    ],
                    [
                        'operation_id' => $opId,
                        'dataset' => 'penduduk',
                        'local_id' => 'local-4',
                        'payload' => [
                            'keluarga_local_id' => $keluargaLocalId,
                            'nik' => '5555666677778888',
                            'nama_lengkap' => 'Test Penduduk',
                            'jenis_kelamin' => 'Laki-laki',
                            'tempat_lahir' => 'Tahuna',
                            'tanggal_lahir' => '1990-01-15',
                            'agama' => 'Kristen',
                            'status_perkawinan' => 'Belum Kawin',
                            'pendidikan' => 'S1',
                            'pekerjaan' => 'Guru',
                            'status_dalam_keluarga' => 'Anak',
                            'status_penduduk' => 'Tetap',
                        ],
                    ],
                ],
            ]);

        $response1->assertJsonPath('results.2.status', 'accepted');

        // Retry with same operation_id → should return accepted (idempotent)
        $response2 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/mobile/sync/push', [
                'device_id' => 'test-device',
                'operations' => [
                    [
                        'operation_id' => $opId,
                        'dataset' => 'penduduk',
                        'local_id' => 'local-4',
                        'payload' => [
                            'nik' => '5555666677778888',
                            'nama_lengkap' => 'Test Penduduk',
                            'jenis_kelamin' => 'Laki-laki',
                            'tempat_lahir' => 'Tahuna',
                            'tanggal_lahir' => '1990-01-15',
                            'agama' => 'Kristen',
                            'status_perkawinan' => 'Belum Kawin',
                            'pendidikan' => 'S1',
                            'pekerjaan' => 'Guru',
                            'status_dalam_keluarga' => 'Anak',
                            'status_penduduk' => 'Tetap',
                        ],
                    ],
                ],
            ]);

        $response2->assertJsonPath('results.0.status', 'accepted');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function keluarga(): \App\Models\Keluarga
    {
        static $keluarga = null;

        if ($keluarga === null) {
            $kecamatan = Kecamatan::factory()->create();
            $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
            $rumah = Rumah::factory()->create(['desa_id' => $desa->id]);
            $keluarga = \App\Models\Keluarga::factory()->create(['rumah_id' => $rumah->id]);
        }

        return $keluarga;
    }

    private function setupUser(): User
    {
        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $rumah = Rumah::factory()->create(['desa_id' => $desa->id]);
        $user = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $user->assignRole('admin_desa');

        return $user;
    }

    private function keluargaForUser(User $user): \App\Models\Keluarga
    {
        // Create a keluarga in the user's scope so authorization passes
        $rumah = Rumah::factory()->create(['desa_id' => $user->desa_id]);
        return \App\Models\Keluarga::factory()->create(['rumah_id' => $rumah->id]);
    }

    private function validPendudukPayload(string $nik, int $keluargaId): array
    {
        return [
            'keluarga_id' => $keluargaId,
            'nik' => $nik,
            'nama_lengkap' => 'Test Penduduk',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Tahuna',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Kristen',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Guru',
            'status_dalam_keluarga' => 'Anak',
            'status_penduduk' => 'Tetap',
        ];
    }
}