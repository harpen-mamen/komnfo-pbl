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
            'sekolah', 'puskes', 'infrastruktur', 'jalan', 'jembatan',
            'pelabuhan', 'bandara', 'tempat_ibadah', 'pasar', 'umkm',
            'pertanian', 'perikanan', 'submission', 'workflow', 'publication',
            'dashboard', 'reporting', 'audit', 'monitoring', 'api', 'berita', 'portal_content',
        ];

        // OPD-specific modules for Sangihe
        $opdModules = [
            'kesehatan', 'pendidikan', 'pu', 'sosial', 'perdagangan', 'kependudukan',
            'lingkungan', 'pertanian', 'hubungan', 'pariwisata', 'pemuda', 'kepegawaian',
            'perencanaan', 'keuangan', 'bencana', 'kominfo', 'investasi', 'perkebunan',
            'pengendalian_penduduk', 'pemberdayaan_perempuan', 'pemberdayaan_masyarakat',
            'ketenagakerjaan', 'ketahanan_pangan', 'perikanan', 'perpustakaan',
            'perkim', 'koperasi', 'pengawasan', 'kesbangpol', 'perbatasan',
            'satpolpp', 'setda', 'setwan',
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

        // Create roles from AppRole enum
        $roles = collect(AppRole::values())
            ->mapWithKeys(fn (string $name) => [$name => Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'])]);

        // Get all permissions
        $all = Permission::query()->pluck('name')->all();

        // ========================
        // SUPER ADMIN - full access
        // ========================
        $roles[AppRole::SUPER_ADMIN->value]->syncPermissions($all);

        // ========================
        // ADMIN - full access (limited)
        // ========================
        $roles[AppRole::ADMIN->value]->syncPermissions($all);

        // ========================
        // KABUPATEN LEVEL ROLES
        // ========================

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
                ->orWhereIn('name', ['users.view', 'users.create', 'users.update', 'wilayah.view', 'opd.view', 'berita.%']);
        })->pluck('name'));

        // Admin Portal manages editorial content only; dataset values remain owned by OPDs and Kominfo.
        $roles[AppRole::ADMIN_PORTAL->value]->syncPermissions(
            Permission::query()->where('name', 'like', 'portal_content.%')->pluck('name')
        );

        // Walidata is the operational name for the Kabupaten data steward.
        $roles[AppRole::WALIDATA->value]->syncPermissions($roles[AppRole::ADMIN_KOMINFO->value]->permissions);

        // Admin Bupati
        $roles[AppRole::ADMIN_BUPATI->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhere('name', 'like', 'monitoring.%')
                ->orWhereIn('name', ['opd.view', 'wilayah.view']);
        })->pluck('name'));

        // Admin Wakil Bupati
        $roles[AppRole::ADMIN_WAKIL_BUPATI->value]->syncPermissions($roles[AppRole::ADMIN_BUPATI->value]->permissions);

        // ========================
        // OPD LEVEL ROLES
        // ========================

        // Admin OPD (generic)
        $roles[AppRole::ADMIN_OPD->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'monitoring.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['opd.view', 'submission.view']);
        })->pluck('name'));

        // Kepala Dinas - full access di OPD-nya
        $roles[AppRole::KEPALA_DINAS->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'monitoring.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhere('name', 'like', 'submission.%')
                ->orWhere('name', 'like', 'workflow.%')
                ->orWhereIn('name', ['opd.view', 'users.view']);
        })->pluck('name'));

        // Operator OPD
        $roles[AppRole::OPERATOR_OPD->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'submission.%')
                ->orWhereIn('name', ['opd.view']);
        })->pluck('name'));

        // Admin Berita - akses penuh untuk konten publik
        $roles[AppRole::ADMIN_BERITA->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'berita.%')
                ->orWhere('name', 'like', 'publication.%')
                ->orWhere('name', 'like', 'dashboard.%')
                ->orWhereIn('name', ['users.view', 'users.update']);
        })->pluck('name'));

        // ========================
        // KECAMATAN LEVEL ROLES
        // ========================

        // Admin Kecamatan
        $roles[AppRole::ADMIN_KECAMATAN->value]->syncPermissions(Permission::query()->where(function ($query): void {
            $query->where('name', 'like', 'dashboard.%')
                ->orWhere('name', 'like', 'reporting.%')
                ->orWhereIn('name', ['wilayah.view', 'submission.view', 'submission.review', 'workflow.view', 'workflow.review']);
        })->pluck('name'));

        // Field staff can capture and submit spatial house records
        $roles[AppRole::ADMIN_LAPANGAN->value]->syncPermissions([
            'rumah.view', 'rumah.create', 'rumah.update',
            'wilayah.view', 'submission.view', 'submission.create', 'submission.submit',
            'workflow.view', 'dashboard.view',
        ]);

        // Admin Desa - hanya input data rumah
        $roles[AppRole::ADMIN_DESA->value]->syncPermissions([
            'rumah.view', 'rumah.create', 'rumah.update',
            'wilayah.view', 'submission.view', 'submission.create', 'submission.submit',
            'workflow.view', 'dashboard.view',
        ]);

        // ========================
        // OPD-SPECIFIC ROLES
        // ========================

        // Admin Dinkes - Kesehatan
        $roles[AppRole::ADMIN_DINKES->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kesehatan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DINKES->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kesehatan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Dispendik - Pendidikan
        $roles[AppRole::ADMIN_DISPEND->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pendidikan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISPEND->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pendidikan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin DPUPR - PU
        $roles[AppRole::ADMIN_DPU->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pu.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DPU->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pu.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Dinsos - Sosial
        $roles[AppRole::ADMIN_DINSOS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'sosial.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DINSOS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'sosial.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Disperindag - Perdagangan
        $roles[AppRole::ADMIN_DISPERINDAG->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perdagangan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISPERINDAG->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perdagangan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Dukcapil - Kependudukan
        $roles[AppRole::ADMIN_DUKCAPIL->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kependudukan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DUKCAPIL->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kependudukan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin DLH - Lingkungan
        $roles[AppRole::ADMIN_DLH->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'lingkungan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DLH->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'lingkungan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Distan - Pertanian
        $roles[AppRole::ADMIN_DISTAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pertanian.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISTAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pertanian.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Dishub -Hubungan
        $roles[AppRole::ADMIN_DISHUB->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'hubungan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISHUB->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'hubungan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Distparbud - Pariwisata
        $roles[AppRole::ADMIN_DISTPAR->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pariwisata.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISTPAR->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pariwisata.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Dispora - Pemuda
        $roles[AppRole::ADMIN_DISPORA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pemuda.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISPORA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pemuda.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin BKPSDMD - Kepegawaian
        $roles[AppRole::ADMIN_BKPSDMD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kepegawaian.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BKPSDMD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kepegawaian.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Bappeda - Perencanaan
        $roles[AppRole::ADMIN_BAPPEDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perencanaan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BAPPEDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perencanaan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Bapenda - Keuangan
        $roles[AppRole::ADMIN_BAPENDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'keuangan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BAPENDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'keuangan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin BPBD - Bencana
        $roles[AppRole::ADMIN_BPBD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'bencana.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BPBD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'bencana.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Diskominfo - Kominfo
        $roles[AppRole::ADMIN_DISKOMINFO->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kominfo.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%')
                    ->orWhere('name', 'like', 'api.%')
                    ->orWhere('name', 'like', 'publication.%')
                    ->orWhere('name', 'like', 'berita.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISKOMINFO->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kominfo.%')
                    ->orWhere('name', 'like', 'api.%');
            })->pluck('name')
        );

        // Admin DPMTSP - Investasi
        $roles[AppRole::ADMIN_DPMTSP->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'investasi.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DPMTSP->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'investasi.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // Admin Distanak - Perkebunan
        $roles[AppRole::ADMIN_DISTAMAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perkebunan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISTAMAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perkebunan.%')
                    ->orWhere('name', 'like', 'submission.%');
            })->pluck('name')
        );

        // ========================
        // FASE 1: OPD BARU - 14 OPD Tambahan
        // ========================

        // Admin Setda
        $roles[AppRole::ADMIN_SETDA->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'setda.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_SETDA->value]->syncPermissions(['setda.view', 'submission.view']);

        // Admin Setwan
        $roles[AppRole::ADMIN_SETWAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'setwan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_SETWAN->value]->syncPermissions(['setwan.view', 'submission.view']);

        // Admin DPPKB
        $roles[AppRole::ADMIN_DPPKB->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pengendalian_penduduk.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DPPKB->value]->syncPermissions(['pengendalian_penduduk.view', 'submission.view']);

        // Admin DP3A
        $roles[AppRole::ADMIN_DP3A->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pemberdayaan_perempuan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DP3A->value]->syncPermissions(['pemberdayaan_perempuan.view', 'submission.view']);

        // Admin DPMD
        $roles[AppRole::ADMIN_DPMD->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pemberdayaan_masyarakat.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DPMD->value]->syncPermissions(['pemberdayaan_masyarakat.view', 'submission.view']);

        // Admin Disnaker
        $roles[AppRole::ADMIN_DISNAKER->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'ketenagakerjaan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_DISNAKER->value]->syncPermissions(['ketenagakerjaan.view', 'submission.view']);

        // Admin Ketapang
        $roles[AppRole::ADMIN_KETAPANG->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'ketahanan_pangan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_KETAPANG->value]->syncPermissions(['ketahanan_pangan.view', 'submission.view']);

        // AdminPerikanan
        $roles[AppRole::ADMIN_PERIKANAN->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perikanan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_PERIKANAN->value]->syncPermissions(['perikanan.view', 'submission.view']);

        // Admin Perpus
        $roles[AppRole::ADMIN_PERPUS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perpustakaan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_PERPUS->value]->syncPermissions(['perpustakaan.view', 'submission.view']);

        // Admin Perkim
        $roles[AppRole::ADMIN_PERKIM->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perkim.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_PERKIM->value]->syncPermissions(['perkim.view', 'submission.view']);

        // Admin Koprasi
        $roles[AppRole::ADMIN_KOPERASI->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'koperasi.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_KOPERASI->value]->syncPermissions(['koperasi.view', 'submission.view']);

        // Admin Inspektorat
        $roles[AppRole::ADMIN_INSPEKTORAT->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'pengawasan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'audit.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_INSPEKTORAT->value]->syncPermissions(['pengawasan.view', 'submission.view']);

        // Admin Kesbangpol
        $roles[AppRole::ADMIN_KESBANGPOL->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'kesbangpol.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_KESBANGPOL->value]->syncPermissions(['kesbangpol.view', 'submission.view']);

        // Admin Batas
        $roles[AppRole::ADMIN_BATAS->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'perbatasan.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%')
                    ->orWhere('name', 'like', 'submission.%')
                    ->orWhere('name', 'like', 'workflow.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_BATAS->value]->syncPermissions(['perbatasan.view', 'submission.view']);

        // Admin Satpol PP
        $roles[AppRole::ADMIN_SATPOLPP->value]->syncPermissions(
            Permission::query()->where(function ($query): void {
                $query->where('name', 'like', 'satpolpp.%')
                    ->orWhere('name', 'like', 'dashboard.%')
                    ->orWhere('name', 'like', 'reporting.%');
            })->pluck('name')
        );
        $roles[AppRole::OPERATOR_SATPOLPP->value]->syncPermissions(['satpolpp.view', 'submission.view']);

        // ========================
        // PUBLIC - Guest access
        // ========================
        $roles[AppRole::PUBLIC->value]->syncPermissions(['api.view']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
