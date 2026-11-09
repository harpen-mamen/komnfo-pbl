<?php

namespace Tests\Feature;

use App\Models\Opd;
use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinalRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_roles_and_permissions_are_seeded(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertEqualsCanonicalizing(
            array_map(fn ($role) => $role->value, \App\Enums\AppRole::cases()),
            Role::query()->pluck('name')->all()
        );

        $this->assertTrue(Role::findByName('admin_desa')->hasPermissionTo('submission.submit'));
        $this->assertTrue(Role::findByName('admin_kecamatan')->hasPermissionTo('submission.review'));
        $this->assertTrue(Role::findByName('admin_kominfo')->hasPermissionTo('publication.publish'));
    }

    public function test_users_can_hold_desa_and_opd_scope(): void
    {
        $kabupaten = Wilayah::query()->create(['tipe' => Wilayah::TIPE_KABUPATEN, 'nama' => 'Kabupaten Uji']);
        $kecamatan = Wilayah::query()->create(['parent_id' => $kabupaten->id, 'tipe' => Wilayah::TIPE_KECAMATAN, 'nama' => 'Kecamatan Uji']);
        $desa = Wilayah::query()->create(['parent_id' => $kecamatan->id, 'tipe' => Wilayah::TIPE_DESA, 'nama' => 'Desa Uji']);
        $opd = Opd::query()->create(['kode' => 'DINKES', 'nama' => 'Dinas Kesehatan']);

        $desaUser = User::factory()->create(['kecamatan_id' => $kecamatan->id, 'desa_id' => $desa->id]);
        $opdUser = User::factory()->create(['opd_id' => $opd->id]);

        $this->assertTrue($desaUser->desa->is($desa));
        $this->assertTrue($opdUser->opd->is($opd));
    }
}
