<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Opd;
use App\Models\User;
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

        $this->assertEqualsCanonicalizing([
            'super_admin', 'admin_kominfo', 'admin_opd', 'admin_kecamatan', 'admin_desa', 'public', 'admin',
        ], Role::query()->pluck('name')->all());

        $this->assertTrue(Role::findByName('admin_desa')->hasPermissionTo('submission.submit'));
        $this->assertTrue(Role::findByName('admin_kecamatan')->hasPermissionTo('submission.review'));
        $this->assertTrue(Role::findByName('admin_kominfo')->hasPermissionTo('publication.publish'));
    }

    public function test_users_can_hold_desa_and_opd_scope(): void
    {
        $kecamatan = Kecamatan::factory()->create();
        $desa = Desa::factory()->create(['kecamatan_id' => $kecamatan->id]);
        $opd = Opd::query()->create(['kode' => 'DINKES', 'nama' => 'Dinas Kesehatan']);

        $desaUser = User::factory()->create(['kecamatan_id' => $kecamatan->id, 'desa_id' => $desa->id]);
        $opdUser = User::factory()->create(['opd_id' => $opd->id]);

        $this->assertTrue($desaUser->desa->is($desa));
        $this->assertTrue($opdUser->opd->is($opd));
    }
}
