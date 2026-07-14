<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = [
            'users', 'roles', 'wilayah', 'opd', 'rumah', 'keluarga', 'penduduk',
            'sekolah', 'puskesmas', 'infrastruktur', 'jalan', 'jembatan',
            'pelabuhan', 'bandara', 'tempat_ibadah', 'pasar', 'umkm',
            'pertanian', 'perikanan', 'submission', 'workflow', 'publication',
            'dashboard', 'reporting', 'audit', 'monitoring', 'api',
        ];
        $actions = ['view', 'create', 'update', 'delete', 'submit', 'review', 'approve', 'publish', 'export'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $module.'.'.$action, 'guard_name' => 'web']);
            }
        }

        $roles = collect([
            'super_admin', 'admin_kominfo', 'admin_opd', 'admin_kecamatan', 'admin_desa', 'public',
            'admin', // Legacy compatibility.
        ])->mapWithKeys(fn (string $name) => [$name => Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'])]);

        $all = Permission::query()->pluck('name')->all();
        $roles['super_admin']->syncPermissions($all);
        $roles['admin']->syncPermissions($all);

        $roles['admin_kominfo']->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'submission.%')
                ->orWhere('name', 'like', 'workflow.%')
                ->orWhere('name', 'like', 'publication.%')
                ->orWhere('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'monitoring.%')
                ->orWhere('name', 'like', 'audit.%')
                ->orWhere('name', 'like', 'api.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['users.view', 'users.create', 'users.update', 'wilayah.view', 'opd.view']);
        })->pluck('name'));

        $roles['admin_kecamatan']->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['wilayah.view', 'submission.view', 'submission.review', 'workflow.view', 'workflow.review']);
        })->pluck('name'));

        $desaModules = ['rumah', 'keluarga', 'penduduk', 'sekolah', 'puskesmas', 'infrastruktur', 'jalan', 'jembatan', 'pelabuhan', 'bandara', 'tempat_ibadah', 'pasar', 'umkm', 'pertanian', 'perikanan'];
        $desaPermissions = collect($desaModules)
            ->flatMap(fn (string $module) => [$module.'.view', $module.'.create', $module.'.update', $module.'.delete'])
            ->merge(['wilayah.view', 'submission.view', 'submission.create', 'submission.submit', 'workflow.view', 'dashboard.view', 'reporting.view', 'reporting.export'])
            ->unique()
            ->values();
        $roles['admin_desa']->syncPermissions($desaPermissions);

        $roles['admin_opd']->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'monitoring.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['opd.view', 'submission.view']);
        })->pluck('name'));

        $roles['public']->syncPermissions(['api.view']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
