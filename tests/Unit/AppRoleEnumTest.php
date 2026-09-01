<?php

namespace Tests\Unit;

use App\Enums\AppRole;
use Tests\TestCase;

/**
 * SPEC-005: AppRole enum is the SINGLE source of truth for role identifiers.
 *
 * These tests verify enum behavior, label generation, and helper methods.
 */
class AppRoleEnumTest extends TestCase
{
    public function test_values_returns_all_role_strings(): void
    {
        $values = AppRole::values();

        $this->assertContains('super_admin', $values);
        $this->assertContains('admin', $values);
        $this->assertContains('admin_kominfo', $values);
        $this->assertContains('admin_kecamatan', $values);
        $this->assertContains('admin_desa', $values);
        $this->assertContains('public', $values);
    }

    public function test_label_returns_correct_human_label(): void
    {
        $this->assertSame('Super Admin', AppRole::SUPER_ADMIN->label());
        $this->assertSame('Admin Kominfo', AppRole::ADMIN_KOMINFO->label());
        $this->assertSame('Admin Kecamatan', AppRole::ADMIN_KECAMATAN->label());
        $this->assertSame('Admin Desa', AppRole::ADMIN_DESA->label());
        $this->assertSame('Admin Dinkes', AppRole::ADMIN_DINKES->label());
    }

    public function test_can_access_filament_excludes_public(): void
    {
        $this->assertFalse(AppRole::PUBLIC->canAccessFilament());
        $this->assertTrue(AppRole::SUPER_ADMIN->canAccessFilament());
        $this->assertTrue(AppRole::ADMIN_KECAMATAN->canAccessFilament());
        $this->assertTrue(AppRole::ADMIN_DINKES->canAccessFilament());
    }

    public function test_is_available_for_registration_excludes_core_admin_roles(): void
    {
        $this->assertFalse(AppRole::SUPER_ADMIN->isAvailableForRegistration());
        $this->assertFalse(AppRole::ADMIN->isAvailableForRegistration());
        $this->assertFalse(AppRole::ADMIN_KOMINFO->isAvailableForRegistration());
        $this->assertFalse(AppRole::ADMIN_OPD->isAvailableForRegistration());
        $this->assertFalse(AppRole::PUBLIC->isAvailableForRegistration());

        // All kecamatan/desa/OPD roles are available
        $this->assertTrue(AppRole::ADMIN_KECAMATAN->isAvailableForRegistration());
        $this->assertTrue(AppRole::ADMIN_DESA->isAvailableForRegistration());
        $this->assertTrue(AppRole::ADMIN_DINKES->isAvailableForRegistration());
    }

    public function test_opd_module_returns_correct_module(): void
    {
        $this->assertSame('kesehatan', AppRole::ADMIN_DINKES->opdModule());
        $this->assertSame('kesehatan', AppRole::OPERATOR_PUSKESMAS->opdModule());
        $this->assertSame('pendidikan', AppRole::ADMIN_DISPEND->opdModule());
        $this->assertSame('pendidikan', AppRole::OPERATOR_SEKOLAH->opdModule());
        $this->assertSame('pu', AppRole::ADMIN_DPU->opdModule());
        $this->assertSame('sosial', AppRole::ADMIN_DINSOS->opdModule());
        $this->assertSame('ekonomi', AppRole::ADMIN_DISPERINDAG->opdModule());
        $this->assertSame('kependudukan', AppRole::ADMIN_DUKCAPIL->opdModule());
        $this->assertSame('lingkungan', AppRole::ADMIN_DLH->opdModule());

        // Non-OPD roles return null
        $this->assertNull(AppRole::SUPER_ADMIN->opdModule());
        $this->assertNull(AppRole::ADMIN_KECAMATAN->opdModule());
        $this->assertNull(AppRole::ADMIN_DESA->opdModule());
    }

    public function test_is_opd_level_returns_correct_value(): void
    {
        $this->assertTrue(AppRole::ADMIN_DINKES->isOpdLevel());
        $this->assertTrue(AppRole::OPERATOR_PUSKESMAS->isOpdLevel());
        $this->assertTrue(AppRole::ADMIN_OPD->isOpdLevel());

        $this->assertFalse(AppRole::SUPER_ADMIN->isOpdLevel());
        $this->assertFalse(AppRole::ADMIN_KECAMATAN->isOpdLevel());
        $this->assertFalse(AppRole::ADMIN_DESA->isOpdLevel());
    }

    public function test_admin_level_helpers(): void
    {
        $this->assertTrue(AppRole::SUPER_ADMIN->isKabupatenLevel());
        $this->assertTrue(AppRole::ADMIN->isKabupatenLevel());
        $this->assertTrue(AppRole::ADMIN_KOMINFO->isKabupatenLevel());
        $this->assertFalse(AppRole::ADMIN_KECAMATAN->isKabupatenLevel());

        $this->assertTrue(AppRole::ADMIN_KECAMATAN->isKecamatanLevel());
        $this->assertFalse(AppRole::ADMIN_DESA->isKecamatanLevel());

        $this->assertTrue(AppRole::ADMIN_DESA->isDesaLevel());
        $this->assertFalse(AppRole::ADMIN_KECAMATAN->isDesaLevel());
    }

    public function test_available_for_registration_returns_labeled_array(): void
    {
        $available = AppRole::availableForRegistration();

        $this->assertIsArray($available);
        $this->assertArrayHasKey(AppRole::ADMIN_KECAMATAN->value, $available);
        $this->assertIsString($available[AppRole::ADMIN_KECAMATAN->value]);
        $this->assertArrayNotHasKey(AppRole::SUPER_ADMIN->value, $available);
    }

    public function test_filament_access_values_excludes_public(): void
    {
        $values = AppRole::filamentAccessValues();

        $this->assertNotContains(AppRole::PUBLIC->value, $values);
        $this->assertContains(AppRole::ADMIN_KECAMATAN->value, $values);
        $this->assertContains(AppRole::SUPER_ADMIN->value, $values);
    }

    public function test_try_from_returns_null_for_invalid_role(): void
    {
        $this->assertNull(AppRole::tryFrom('invalid_role'));
        $this->assertNull(AppRole::tryFrom(''));
        $this->assertSame(AppRole::SUPER_ADMIN, AppRole::tryFrom('super_admin'));
    }
}
