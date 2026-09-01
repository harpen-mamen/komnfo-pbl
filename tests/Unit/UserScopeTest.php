<?php

namespace Tests\Unit;

use App\Enums\AppRole;
use App\Models\User;
use App\Support\UserScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SPEC-004: UserScope is the SINGLE source of truth for data visibility.
 *
 * These tests verify that UserScope correctly identifies user level
 * (kabupaten, kecamatan, desa, opd) and that role checks delegate to AppRole enum.
 *
 * Note: Tests do not depend on FK constraints (kecamatan_id/desa_id are nullable)
 * since scope logic only checks role membership.
 */
class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed all AppRole roles so Spatie can resolve them
        foreach (AppRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    public function test_is_kabupaten_level_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::SUPER_ADMIN->value);
        $this->actingAs($user);

        $this->assertTrue(UserScope::isKabupatenLevel());
    }

    public function test_is_kabupaten_level_returns_true_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::ADMIN->value);
        $this->actingAs($user);

        $this->assertTrue(UserScope::isKabupatenLevel());
    }

    public function test_is_kabupaten_level_returns_true_for_admin_kominfo(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::ADMIN_KOMINFO->value);
        $this->actingAs($user);

        $this->assertTrue(UserScope::isKabupatenLevel());
    }

    public function test_is_kabupaten_level_returns_false_for_kecamatan_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::ADMIN_KECAMATAN->value);
        $this->actingAs($user);

        $this->assertFalse(UserScope::isKabupatenLevel());
        $this->assertTrue(UserScope::isKecamatanLevel());
    }

    public function test_is_desa_level_returns_true_for_admin_desa(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::ADMIN_DESA->value);
        $this->actingAs($user);

        $this->assertTrue(UserScope::isDesaLevel());
        $this->assertFalse(UserScope::isKecamatanLevel());
        $this->assertFalse(UserScope::isKabupatenLevel());
    }

    public function test_is_opd_level_returns_true_for_opd_roles(): void
    {
        $opdRoles = [
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
            AppRole::ADMIN_DISPEND->value,
            AppRole::OPERATOR_PUSKESMAS->value,
            AppRole::ADMIN_OPD->value,
        ];

        foreach ($opdRoles as $roleName) {
            $user = User::factory()->create();
            $user->assignRole($roleName);
            $this->actingAs($user);

            $this->assertTrue(
                UserScope::isOpdLevel(),
                "Failed asserting that role {$roleName} is OPD level"
            );
        }
    }

    public function test_is_opd_level_returns_false_for_non_opd_roles(): void
    {
        $nonOpdRoles = [
            AppRole::ADMIN_KECAMATAN->value,
            AppRole::ADMIN_DESA->value,
            AppRole::PUBLIC->value,
        ];

        foreach ($nonOpdRoles as $roleName) {
            $user = User::factory()->create();
            $user->assignRole($roleName);
            $this->actingAs($user);

            $this->assertFalse(
                UserScope::isOpdLevel(),
                "Failed asserting that role {$roleName} is NOT OPD level"
            );
        }
    }

    public function test_get_kecamatan_id_returns_null_when_not_set(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::ADMIN_KECAMATAN->value);
        $this->actingAs($user);

        // kecamatan_id is null since we did not set it
        $this->assertNull(UserScope::getKecamatanId());
    }

    public function test_get_desa_id_returns_null_when_not_set(): void
    {
        $user = User::factory()->create();
        $user->assignRole(AppRole::ADMIN_DESA->value);
        $this->actingAs($user);

        $this->assertNull(UserScope::getDesaId());
    }

    public function test_user_returns_null_when_not_authenticated(): void
    {
        $this->assertNull(UserScope::user());
        $this->assertNull(UserScope::getKecamatanId());
        $this->assertNull(UserScope::getDesaId());
        $this->assertFalse(UserScope::isKabupatenLevel());
        $this->assertFalse(UserScope::isKecamatanLevel());
        $this->assertFalse(UserScope::isDesaLevel());
        $this->assertFalse(UserScope::isOpdLevel());
    }
}
