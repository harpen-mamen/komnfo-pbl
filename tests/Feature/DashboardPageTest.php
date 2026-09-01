<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_kominfo_can_load_the_dashboard(): void
    {
        Role::firstOrCreate(['name' => 'admin_kominfo', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('admin_kominfo');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }
}
