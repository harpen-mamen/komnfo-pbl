<?php

namespace Database\Seeders;

use App\Enums\AppRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Core modules
        $modules = [
            'users', 'roles', 'wilayah', 'opd', 'rumah', 'keluarga', 'penduduk',
            'sekolah', 'puskesmas', 'infrastruktur', 'jalan', 'jembatan',
            'pelabuhan', 'bandara', 'tempat_ibadah', 'pasar', 'umkm',
            'pertanian', 'perikanan', 'submission', 'workflow', 'publication',
            'dashboard', 'reporting', 'audit', 'monitoring', 'api',
        ];

        // OPD-specific modules for Sangihe
        $opdModules = [
            'kesehatan', 'pendidikan', 'pu', 'sosial', 'perdagangan', 'kependudukan',
            'lingkungan', 'pertanian', 'hubungan', 'pariwisata', 'pemuda', 'kepegawaian',
            'perencanaan', 'keuangan', 'bencana', 'kominfo', 'investasi', 'perkebunan',
        ];

        $actions = ['view', 'create', 'update', 'delete', 'submit', 'review', 'approve', 'publish', 'export'];

        // Create core permissions
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $module.'.'.$action, 'guard_name' => 'web']);
            }
        }

        // Create OPD permissions
        foreach ($opdModules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $module.'.'.$action, 'guard_name' => 'web']);
            }
        }

        // SPEC-005: Use AppRole enum (single source of truth for role strings).
        // Keep 'public' role in the collection but don't try to attach all permissions to it
        // (it only has 'api.view' at the end of this seeder).
        $roles = collect(AppRole::values())
            ->mapWithKeys(fn (string $name) => [$name => Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'])]);

        // Get all permissions
        $all = Permission::query()->pluck('name')->all();

        // Super Admin - full access
        $roles[AppRole::SUPER_ADMIN->value]->syncPermissions($all);

        // Admin - full access (limited)
        $roles[AppRole::ADMIN->value]->syncPermissions($all);

        // Admin Kominfo - monitoring, validation, export
        $roles[AppRole::ADMIN_KOMINFO->value]->syncPermissions(Permission::query()->where(function ($query): void {
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

        // Admin Kecamatan
        $roles[AppRole::ADMIN_KECAMATAN->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['wilayah.view', 'submission.view', 'submission.review', 'workflow.view', 'workflow.review']);
        })->pluck('name'));

        // Admin Desa
        $desaModules = ['rumah', 'keluarga', 'penduduk', 'sekolah', 'puskesmas', 'infrastruktur', 'jalan', 'jembatan', 'pelabuhan', 'bandara', 'tempat_ibadah', 'pasar', 'umkm', 'pertanian', 'perikanan'];
        $desaPermissions = collect($desaModules)
            ->flatMap(fn (string $module) => [$module.'.view', $module.'.create', $module.'.update', $module.'.delete'])
            ->merge(['wilayah.view', 'submission.view', 'submission.create', 'submission.submit', 'workflow.view', 'dashboard.view', 'reporting.view', 'reporting.export'])
            ->unique()
            ->values();
        $roles[AppRole::ADMIN_DESA->value]->syncPermissions($desaPermissions);

        // Admin OPD (generic)
        $roles[AppRole::ADMIN_OPD->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'monitoring.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['opd.view', 'submission.view']);
        })->pluck('name'));

        // ========== OPD-SPECIFIC ROLES - KABUPATEN KEPULAUAN SANGIHE ==========

        // Admin Dinkes - Full access to kesehatan module
        $roles[AppRole::ADMIN_DINKES->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kesehatan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhereIn('name', ['puskesmas.view', 'puskesmas.create', 'puskesmas.update']);
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DINKES->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kesehatan.%')
                    ->orWhereIn('name', ['puskesmas.view', 'puskesmas.create', 'puskesmas.update']);
            })->pluck('name')
        );

        // Admin Disdik - Pendidikan
        $roles[AppRole::ADMIN_DISPEND->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pendidikan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhereIn('name', ['sekolah.view', 'sekolah.create', 'sekolah.update']);
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISPEND->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pendidikan.%')
                    ->orWhereIn('name', ['sekolah.view', 'sekolah.create', 'sekolah.update']);
            })->pluck('name')
        );

        // Admin DPUPR - PU
        $roles[AppRole::ADMIN_DPU->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pu.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhereIn('name', ['infrastruktur.view', 'infrastruktur.create', 'infrastruktur.update', 'infrastruktur.delete'])
                    ->orWhereIn('name', ['jalan.view', 'jalan.create', 'jalan.update', 'jalan.delete'])
                    ->orWhereIn('name', ['jembatan.view', 'jembatan.create', 'jembatan.update', 'jembatan.delete']);
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DPU->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pu.%')
                    ->orWhereIn('name', ['infrastruktur.view', 'infrastruktur.create', 'infrastruktur.update'])
                    ->orWhereIn('name', ['jalan.view', 'jalan.create', 'jalan.update'])
                    ->orWhereIn('name', ['jembatan.view', 'jembatan.create', 'jembatan.update']);
            })->pluck('name')
        );

        // Admin Dinsos - Sosial
        $roles[AppRole::ADMIN_DINSOS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'sosial.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DINSOS->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'sosial.%')->pluck('name')
        );

        // Admin Disperindag - Perdagangan
        $roles[AppRole::ADMIN_DISPERINDAG->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perdagangan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhereIn('name', ['umkm.view', 'umkm.create', 'umkm.update', 'umkm.delete'])
                    ->orWhereIn('name', ['pasar.view', 'pasar.create', 'pasar.update', 'pasar.delete']);
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISPERINDAG->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perdagangan.%')
                    ->orWhereIn('name', ['umkm.view', 'umkm.create', 'umkm.update'])
                    ->orWhereIn('name', ['pasar.view', 'pasar.create', 'pasar.update']);
            })->pluck('name')
        );

        // Admin Dukcapil - Kependudukan
        $roles[AppRole::ADMIN_DUKCAPIL->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kependudukan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhereIn('name', ['penduduk.view', 'penduduk.create', 'penduduk.update', 'penduduk.delete'])
                    ->orWhereIn('name', ['keluarga.view', 'keluarga.create', 'keluarga.update', 'keluarga.delete']);
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DUKCAPIL->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kependudukan.%')
                    ->orWhereIn('name', ['penduduk.view', 'penduduk.create', 'penduduk.update'])
                    ->orWhereIn('name', ['keluarga.view', 'keluarga.create', 'keluarga.update']);
            })->pluck('name')
        );

        // Admin DLH - Lingkungan
        $roles[AppRole::ADMIN_DLH->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'lingkungan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DLH->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'lingkungan.%')->pluck('name')
        );

        // Admin DISTAN - Pertanian
        $roles[AppRole::ADMIN_DISTAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pertanian.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISTAN->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'pertanian.%')->pluck('name')
        );

        // Admin Dishub - Hubungan
        $roles[AppRole::ADMIN_DISHUB->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'hubungan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISHUB->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'hubungan.%')->pluck('name')
        );

        // Admin Distpar - Pariwisata
        $roles[AppRole::ADMIN_DISTPAR->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pariwisata.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISTPAR->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'pariwisata.%')->pluck('name')
        );

        // Admin Dispora - Pemuda
        $roles[AppRole::ADMIN_DISPORA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pemuda.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISPORA->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'pemuda.%')->pluck('name')
        );

        // Admin BKPSDMD - Kepegawaian
        $roles[AppRole::ADMIN_BKPSDMD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kepegawaian.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BKPSDMD->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'kepegawaian.%')->pluck('name')
        );

        // Admin Bappeda - Perencanaan
        $roles[AppRole::ADMIN_BAPPEDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perencanaan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BAPPEDA->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'perencanaan.%')->pluck('name')
        );

        // Admin Bapenda - Keuangan
        $roles[AppRole::ADMIN_BAPENDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'keuangan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BAPENDA->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'keuangan.%')->pluck('name')
        );

        // Admin BPBD - Bencana
        $roles[AppRole::ADMIN_BPBD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'bencana.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BPBD->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'bencana.%')->pluck('name')
        );

        // Admin Diskominfo - Kominfo
        $roles[AppRole::ADMIN_DISKOMINFO->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kominfo.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'api.%')
                    ->orWhere('name', 'like', 'publication.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISKOMINFO->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kominfo.%')
                    ->orWhere('name', 'like', 'api.%');
            })->pluck('name')
        );

        // Admin Dpmptsp - Investasi
        $roles[AppRole::ADMIN_DPMTSP->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'investasi.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DPMTSP->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'investasi.%')->pluck('name')
        );

        // Admin Distaman - Perkebunan
        $roles[AppRole::ADMIN_DISTAMAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perkebunan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISTAMAN->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'perkebunan.%')->pluck('name')
        );

        // ========== PUSKES & SEKOLAH-SPECIFIC ROLES ==========

        // Admin Puskesmas - full access to puskes facility management
        $roles[AppRole::ADMIN_PUSKESMAS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'puskes.%')
                    ->orWhere('name', 'like', 'kesehatan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Operator Puskesmas - CRUD on puskes data
        $roles[AppRole::OPERATOR_PUSKESMAS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'puskes.%')
                    ->orWhere('name', 'like', 'kesehatan.%')
                    ->orWhere('name', 'like', 'submission.create');
            })->pluck('name')
        );

        // Admin Sekolah Kecamatan - full access to sekolah management in their kecamatan
        $roles[AppRole::ADMIN_SEKOLAH->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'sekolah.%')
                    ->orWhere('name', 'like', 'pendidikan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Operator Sekolah Kecamatan - CRUD on sekolah data
        $roles[AppRole::OPERATOR_SEKOLAH->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'sekolah.%')
                    ->orWhere('name', 'like', 'pendidikan.%')
                    ->orWhere('name', 'like', 'submission.create');
            })->pluck('name')
        );

        // Public
        $roles[AppRole::PUBLIC->value]->syncPermissions(['api.view']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
