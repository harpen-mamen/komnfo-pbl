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

class DebugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_desa', 'guard_name' => 'web']);
    }

    public function test_debug(): void
    {
        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $user = User::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'desa_id' => $desa->id,
        ]);
        $user->assignRole('admin_desa');

        $rumahLocalId = 'local-rumah-'.Str::random(6);
        $keluargaLocalId = 'local-keluarga-'.Str::random(6);
        $rumahKode = 'R-TEST-'.Str::random(6);

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
                            'kode_rumah' => $rumahKode,
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
                            'nama_kepala_keluarga' => 'Test Kepala',
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

        dump($response->json());
        $this->assertTrue(true);
    }
}
