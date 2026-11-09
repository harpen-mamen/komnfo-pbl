<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create roles (reuse existing roles)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $kominfoRole = Role::firstOrCreate(['name' => 'admin_kominfo', 'guard_name' => 'web']);
        $kecamatanRole = Role::firstOrCreate(['name' => 'admin_kecamatan', 'guard_name' => 'web']);

        $kecamatans = Wilayah::query()
            ->where('tipe', Wilayah::TIPE_KECAMATAN)
            ->where('aktif', true)
            ->orderBy('id')
            ->take(2)
            ->get();

        // Create Super Admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sipdt.test'],
            [
                'name' => 'Super Administrator',
                'password' => 'password',
                // Super admin typically doesn't belong to a specific kecamatan/desa
            ]
        );
        $superAdmin->syncRoles([$superAdminRole]);

        // Create Admin Kominfo user
        $adminKominfo = User::updateOrCreate(
            ['email' => 'kominfo@sipdt.test'],
            [
                'name' => 'Administrator Kominfo',
                'password' => 'password',
                // Kominfo typically operates at kabupaten level
            ]
        );
        $adminKominfo->syncRoles([$kominfoRole]);

        foreach ($kecamatans as $index => $kecamatan) {
            $number = $index + 1;
            $adminKecamatan = User::updateOrCreate(
                ['email' => "kecamatan{$number}@sipdt.test"],
                [
                    'name' => "Administrator {$kecamatan->nama}",
                    'password' => 'password',
                    'kecamatan_id' => $kecamatan->id,
                ]
            );
            $adminKecamatan->syncRoles([$kecamatanRole]);
        }
    }
}
