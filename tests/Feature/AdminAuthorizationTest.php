<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_kominfo_cannot_manage_privileged_accounts_or_assign_privileged_roles(): void
    {
        foreach (['admin_kominfo', 'admin_kecamatan', 'super_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $kominfo = User::factory()->create();
        $kominfo->assignRole('admin_kominfo');

        $regionalAdmin = User::factory()->create();
        $regionalAdmin->assignRole('admin_kecamatan');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($kominfo);

        $this->assertTrue(UserResource::canCreate());
        $this->assertTrue(UserResource::canEdit($regionalAdmin));
        $this->assertFalse(UserResource::canEdit($superAdmin));
        $this->assertTrue(UserResource::canAssignRole('admin_kecamatan'));
        $this->assertFalse(UserResource::canAssignRole('super_admin'));
    }
}
