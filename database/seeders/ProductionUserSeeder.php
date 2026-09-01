<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        
        // Get or create wilayah structure
        $kabupaten = Kabupaten::firstOrCreate([
            'nama' => 'Kabupaten Kabupaten Utama'
        ]);
        
        // Create two kecamatan for testing
        $kecamatan1 = Kecamatan::firstOrCreate([
            'kabupaten_id' => $kabupaten->id,
            'nama' => 'Kecamatan Utara'
        ]);
        
        $kecamatan2 = Kecamatan::firstOrCreate([
            'kabupaten_id' => $kabupaten->id,
            'nama' => 'Kecamatan Selatan'
        ]);
        
        // Create or update desa for each kecamatan
        $desa1 = Desa::firstOrCreate([
            'kecamatan_id' => $kecamatan1->id,
            'nama' => 'Desa Utara',
            'kode' => '7171021001'
        ]);
        
        $desa2 = Desa::firstOrCreate([
            'kecamatan_id' => $kecamatan2->id,
            'nama' => 'Desa Selatan',
            'kode' => '7171022001'
        ]);
        
        // Create Super Admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sipdt.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                // Super admin typically doesn't belong to a specific kecamatan/desa
            ]
        );
        $superAdmin->syncRoles([$superAdminRole]);
        
        // Create Admin Kominfo user
        $adminKominfo = User::updateOrCreate(
            ['email' => 'kominfo@sipdt.test'],
            [
                'name' => 'Administrator Kominfo',
                'password' => Hash::make('password'),
                // Kominfo typically operates at kabupaten level
            ]
        );
        $adminKominfo->syncRoles([$kominfoRole]);
        
        // Create Admin Kecamatan 1 user
        $adminKecamatan1 = User::updateOrCreate(
            ['email' => 'kecamatan1@sipdt.test'],
            [
                'name' => 'Administrator Kecamatan Utara',
                'password' => Hash::make('password'),
                'kecamatan_id' => $kecamatan1->id,
            ]
        );
        $adminKecamatan1->syncRoles([$kecamatanRole]);
        
        // Create Admin Kecamatan 2 user
        $adminKecamatan2 = User::updateOrCreate(
            ['email' => 'kecamatan2@sipdt.test'],
            [
                'name' => 'Administrator Kecamatan Selatan',
                'password' => Hash::make('password'),
                'kecamatan_id' => $kecamatan2->id,
            ]
        );
        $adminKecamatan2->syncRoles([$kecamatanRole]);
    }
}
