<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\DynamicDatasetResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentUpgradeTest extends TestCase
{
    public function test_login_renders_with_the_current_panel_and_theme(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('css/filament/admin.css', false);
    }

    public function test_existing_dataset_urls_and_custom_dashboard_are_preserved(): void
    {
        $this->assertSame(url('/admin/dataset-values'), DynamicDatasetResource::getUrl('index', panel: 'admin'));
        $this->assertSame(url('/admin'), Dashboard::getUrl(panel: 'admin'));
        $this->assertSame(Dashboard::class, app('router')->getRoutes()->getByName('filament.admin.pages.dashboard')->getActionName());
    }

    public function test_legacy_policy_and_panel_use_the_same_access_decision(): void
    {
        foreach (['admin_kominfo' => true, 'admin_opd' => true, 'citizen' => false] as $role => $expected) {
            $user = new User;
            $user->setRelation('roles', new Collection([new Role(['name' => $role, 'guard_name' => 'web'])]));
            $this->assertSame($expected, $user->canAccessFilament(), $role);
            $this->assertSame($expected, $user->canAccessPanel(Filament::getPanel('admin')), $role);
        }
    }

    public function test_the_checker_accepts_filament_traits(): void
    {
        $this->artisan('filament:check')->assertSuccessful();
    }
}
