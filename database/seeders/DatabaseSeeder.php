<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Opd;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            OpdSeeder::class,
            ProductionUserSeeder::class,
            SangiheTestDataSeeder::class,
            FasilitasKesehatanSeeder::class,
            SekolahSeeder::class,
            EducationFacilityDummySeeder::class,
            SchoolDummySeeder::class,
            DiseaseDummySeeder::class,
        ]);

        // Create/Find main roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $kominfoRole = Role::firstOrCreate(['name' => 'admin_kominfo', 'guard_name' => 'web']);
        $opdRole = Role::firstOrCreate(['name' => 'admin_opd', 'guard_name' => 'web']);
        $adminKecamatanRole = Role::firstOrCreate(['name' => 'admin_kecamatan', 'guard_name' => 'web']);
        $adminDesaRole = Role::firstOrCreate(['name' => 'admin_desa', 'guard_name' => 'web']);

        // OPD Admin roles
        $adminKesehatanRole = Role::firstOrCreate(['name' => 'admin_kesehatan', 'guard_name' => 'web']);
        $operatorKesehatanRole = Role::firstOrCreate(['name' => 'operator_kesehatan', 'guard_name' => 'web']);
        $adminPendidikanRole = Role::firstOrCreate(['name' => 'admin_pendidikan', 'guard_name' => 'web']);
        $operatorPendidikanRole = Role::firstOrCreate(['name' => 'operator_pendidikan', 'guard_name' => 'web']);
        $adminPURole = Role::firstOrCreate(['name' => 'admin_pu', 'guard_name' => 'web']);
        $operatorPURole = Role::firstOrCreate(['name' => 'operator_pu', 'guard_name' => 'web']);
        $adminSosialRole = Role::firstOrCreate(['name' => 'admin_sosial', 'guard_name' => 'web']);
        $operatorSosialRole = Role::firstOrCreate(['name' => 'operator_sosial', 'guard_name' => 'web']);
        $adminEkonomiRole = Role::firstOrCreate(['name' => 'admin_ekonomi', 'guard_name' => 'web']);
        $operatorEkonomiRole = Role::firstOrCreate(['name' => 'operator_ekonomi', 'guard_name' => 'web']);
        $adminKependudukanRole = Role::firstOrCreate(['name' => 'admin_kependudukan', 'guard_name' => 'web']);
        $operatorKependudukanRole = Role::firstOrCreate(['name' => 'operator_kependudukan', 'guard_name' => 'web']);
        $adminLingkunganRole = Role::firstOrCreate(['name' => 'admin_lingkungan', 'guard_name' => 'web']);
        $operatorLingkunganRole = Role::firstOrCreate(['name' => 'operator_lingkungan', 'guard_name' => 'web']);

        // Find OPDs
        $dinkes = Opd::where('kode', 'DINKES')->first();
        $disdik = Opd::where('kode', 'DISDIK')->first();
        $dpupr = Opd::where('kode', 'DPUPR')->first();
        $dinsos = Opd::where('kode', 'DINSOS')->first();
        $disperindag = Opd::where('kode', 'DISPERINDAG')->first();
        $dukcapil = Opd::where('kode', 'DUKCAPIL')->first();
        $dlh = Opd::where('kode', 'DLH')->first();

        // Create wilayah (if not exists)
        $kabupaten = Kabupaten::firstOrCreate(['nama' => 'Kabupaten Kepulauan Sangihe']);
        $kecamatan = Kecamatan::firstOrCreate(['kabupaten_id' => $kabupaten->id, 'nama' => 'Tahuna']);
        $desa = Desa::firstOrCreate(['kecamatan_id' => $kecamatan->id, 'nama' => 'Soataloara I'], ['kode' => '7171012001']);
        Desa::firstOrCreate(['kecamatan_id' => $kecamatan->id, 'nama' => 'Soataloara II'], ['kode' => '7171012002']);

        // ========== CREATE USERS ==========

        // Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@sipdt.test'],
            ['name' => 'Admin SIPDT', 'password' => Hash::make('password')]
        );
        $admin->assignRole($adminRole);
        $admin->assignRole($superAdminRole);

        // Kominfo Admin
        $kominfo = User::updateOrCreate(
            ['email' => 'kominfo@sipdt.test'],
            ['name' => 'Admin Kominfo', 'password' => Hash::make('password')]
        );
        $kominfo->syncRoles([$kominfoRole]);

        // Admin Kecamatan
        $adminKecamatan = User::updateOrCreate(
            ['email' => 'admin.kecamatan@sipdt.test'],
            ['name' => 'Admin Kecamatan Tahuna', 'password' => Hash::make('password'), 'kecamatan_id' => $kecamatan->id]
        );
        $adminKecamatan->forceFill(['kecamatan_id' => $kecamatan->id])->save();
        $adminKecamatan->assignRole($adminKecamatanRole);

        // Admin Desa
        $adminDesa = User::updateOrCreate(
            ['email' => 'admin.desa@sipdt.test'],
            [
                'name' => 'Admin Desa Soataloara I',
                'password' => Hash::make('password'),
                'kecamatan_id' => $kecamatan->id,
                'desa_id' => $desa->id,
            ]
        );
        $adminDesa->syncRoles([$adminDesaRole]);

        // ========== OPD ADMIN USERS ==========

        // Admin Kesehatan
        if ($dinkes) {
            $adminKesehatan = User::updateOrCreate(
                ['email' => 'admin.kesehatan@sipdt.test'],
                [
                    'name' => 'Admin Kesehatan',
                    'password' => Hash::make('password'),
                    'opd_id' => $dinkes->id,
                    'jabatan' => 'Kabid Data dan Informasi',
                ]
            );
            $adminKesehatan->syncRoles([$adminKesehatanRole]);

            // Operator Kesehatan
            $operatorKesehatan = User::updateOrCreate(
                ['email' => 'operator.kesehatan@sipdt.test'],
                [
                    'name' => 'Operator Kesehatan',
                    'password' => Hash::make('password'),
                    'opd_id' => $dinkes->id,
                    'jabatan' => 'Pengelola Data Kesehatan',
                ]
            );
            $operatorKesehatan->syncRoles([$operatorKesehatanRole]);
        }

        // Admin Pendidikan
        if ($disdik) {
            $adminPendidikan = User::updateOrCreate(
                ['email' => 'admin.pendidikan@sipdt.test'],
                [
                    'name' => 'Admin Pendidikan',
                    'password' => Hash::make('password'),
                    'opd_id' => $disdik->id,
                    'jabatan' => 'Kabid Pendidikan',
                ]
            );
            $adminPendidikan->syncRoles([$adminPendidikanRole]);

            // Operator Pendidikan
            $operatorPendidikan = User::updateOrCreate(
                ['email' => 'operator.pendidikan@sipdt.test'],
                [
                    'name' => 'Operator Pendidikan',
                    'password' => Hash::make('password'),
                    'opd_id' => $disdik->id,
                    'jabatan' => 'Pengelola Data Pendidikan',
                ]
            );
            $operatorPendidikan->syncRoles([$operatorPendidikanRole]);
        }

        // Admin PU
        if ($dpupr) {
            $adminPU = User::updateOrCreate(
                ['email' => 'admin.pu@sipdt.test'],
                [
                    'name' => 'Admin PU-PR',
                    'password' => Hash::make('password'),
                    'opd_id' => $dpupr->id,
                    'jabatan' => 'Kabid Bina Marga',
                ]
            );
            $adminPU->syncRoles([$adminPURole]);

            $operatorPU = User::updateOrCreate(
                ['email' => 'operator.pu@sipdt.test'],
                [
                    'name' => 'Operator PU-PR',
                    'password' => Hash::make('password'),
                    'opd_id' => $dpupr->id,
                    'jabatan' => 'Pengelola Data PU',
                ]
            );
            $operatorPU->syncRoles([$operatorPURole]);
        }

        // Admin Sosial
        if ($dinsos) {
            $adminSosial = User::updateOrCreate(
                ['email' => 'admin.sosial@sipdt.test'],
                [
                    'name' => 'Admin Sosial',
                    'password' => Hash::make('password'),
                    'opd_id' => $dinsos->id,
                    'jabatan' => 'Kabid Perlindungan Sosial',
                ]
            );
            $adminSosial->syncRoles([$adminSosialRole]);

            $operatorSosial = User::updateOrCreate(
                ['email' => 'operator.sosial@sipdt.test'],
                [
                    'name' => 'Operator Sosial',
                    'password' => Hash::make('password'),
                    'opd_id' => $dinsos->id,
                    'jabatan' => 'Pengelola Data Sosial',
                ]
            );
            $operatorSosial->syncRoles([$operatorSosialRole]);
        }

        // Admin Ekonomi
        if ($disperindag) {
            $adminEkonomi = User::updateOrCreate(
                ['email' => 'admin.ekonomi@sipdt.test'],
                [
                    'name' => 'Admin Ekonomi',
                    'password' => Hash::make('password'),
                    'opd_id' => $disperindag->id,
                    'jabatan' => 'Kabid Industri dan Perdagangan',
                ]
            );
            $adminEkonomi->syncRoles([$adminEkonomiRole]);

            $operatorEkonomi = User::updateOrCreate(
                ['email' => 'operator.ekonomi@sipdt.test'],
                [
                    'name' => 'Operator Ekonomi',
                    'password' => Hash::make('password'),
                    'opd_id' => $disperindag->id,
                    'jabatan' => 'Pengelola Data Ekonomi',
                ]
            );
            $operatorEkonomi->syncRoles([$operatorEkonomiRole]);
        }

        // Admin Kependudukan
        if ($dukcapil) {
            $adminKependudukan = User::updateOrCreate(
                ['email' => 'admin.kependudukan@sipdt.test'],
                [
                    'name' => 'Admin Kependudukan',
                    'password' => Hash::make('password'),
                    'opd_id' => $dukcapil->id,
                    'jabatan' => 'Kabid Pendaftaran Penduduk',
                ]
            );
            $adminKependudukan->syncRoles([$adminKependudukanRole]);

            $operatorKependudukan = User::updateOrCreate(
                ['email' => 'operator.kependudukan@sipdt.test'],
                [
                    'name' => 'Operator Kependudukan',
                    'password' => Hash::make('password'),
                    'opd_id' => $dukcapil->id,
                    'jabatan' => 'Pengelola Data Kependudukan',
                ]
            );
            $operatorKependudukan->syncRoles([$operatorKependudukanRole]);
        }

        // Admin Lingkungan
        if ($dlh) {
            $adminLingkungan = User::updateOrCreate(
                ['email' => 'admin.lingkungan@sipdt.test'],
                [
                    'name' => 'Admin Lingkungan',
                    'password' => Hash::make('password'),
                    'opd_id' => $dlh->id,
                    'jabatan' => 'Kabid Lingkungan Hidup',
                ]
            );
            $adminLingkungan->syncRoles([$adminLingkunganRole]);

            $operatorLingkungan = User::updateOrCreate(
                ['email' => 'operator.lingkungan@sipdt.test'],
                [
                    'name' => 'Operator Lingkungan',
                    'password' => Hash::make('password'),
                    'opd_id' => $dlh->id,
                    'jabatan' => 'Pengelola Data Lingkungan',
                ]
            );
            $operatorLingkungan->syncRoles([$operatorLingkunganRole]);
        }

        // ========== PUSKES & SEKOLAH USERS ==========

        // Admin Puskesmas (assign to first puskes)
        $puskesRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_puskesmas', 'guard_name' => 'web']);
        $operatorPuskesRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator_puskesmas', 'guard_name' => 'web']);
        $sekolahRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_sekolah', 'guard_name' => 'web']);
        $operatorSekolahRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator_sekolah', 'guard_name' => 'web']);

        $firstPuskes = \App\Models\FasilitasKesehatan::first();
        $firstKecamatan = \App\Models\Kecamatan::first();

        if ($firstPuskes) {
            $adminPuskes = User::updateOrCreate(
                ['email' => 'admin.puskes@sipdt.test'],
                [
                    'name' => 'Admin Puskesmas ' . $firstPuskes->nama,
                    'password' => Hash::make('password'),
                    'puskes_id' => $firstPuskes->id,
                    'kecamatan_id' => $firstPuskes->kecamatan_id,
                    'jabatan' => 'Admin Puskesmas',
                ]
            );
            $adminPuskes->syncRoles([$puskesRole]);

            $operatorPuskes = User::updateOrCreate(
                ['email' => 'operator.puskes@sipdt.test'],
                [
                    'name' => 'Operator Puskesmas ' . $firstPuskes->nama,
                    'password' => Hash::make('password'),
                    'puskes_id' => $firstPuskes->id,
                    'kecamatan_id' => $firstPuskes->kecamatan_id,
                    'jabatan' => 'Operator Puskesmas',
                ]
            );
            $operatorPuskes->syncRoles([$operatorPuskesRole]);
        }

        if ($firstKecamatan) {
            $adminSekolah = User::updateOrCreate(
                ['email' => 'admin.sekolah@sipdt.test'],
                [
                    'name' => 'Admin Sekolah Kec. ' . $firstKecamatan->nama,
                    'password' => Hash::make('password'),
                    'kecamatan_id' => $firstKecamatan->id,
                    'jabatan' => 'Admin Sekolah Kecamatan',
                ]
            );
            $adminSekolah->syncRoles([$sekolahRole]);

            $operatorSekolah = User::updateOrCreate(
                ['email' => 'operator.sekolah@sipdt.test'],
                [
                    'name' => 'Operator Sekolah Kec. ' . $firstKecamatan->nama,
                    'password' => Hash::make('password'),
                    'kecamatan_id' => $firstKecamatan->id,
                    'jabatan' => 'Operator Sekolah Kecamatan',
                ]
            );
            $operatorSekolah->syncRoles([$operatorSekolahRole]);
        }

        // ========== SAMPLE DATA ==========

        $rumah = Rumah::firstOrCreate(
            ['kode_rumah' => 'SGH-TAH-SOA-0001'],
            [
                'desa_id' => $desa->id,
                'alamat_lengkap' => 'Lindongan I, Soataloara I, Tahuna',
                'latitude' => 3.6154321,
                'longitude' => 125.4932101,
                'status_rumah' => 'Milik Sendiri',
                'keterangan' => 'Rumah contoh untuk modul Admin Kecamatan.',
                'created_by' => $adminKecamatan->id,
            ]
        );

        $keluarga = Keluarga::firstOrCreate(
            ['nomor_kk' => '7171010101010001'],
            [
                'rumah_id' => $rumah->id,
                'nama_kepala_keluarga' => 'Yusuf Manabung',
                'jumlah_anggota' => 3,
                'status_ekonomi' => 'Menengah',
                'created_by' => $adminKecamatan->id,
            ]
        );

        $penduduk = [
            ['7171010101800001', 'Yusuf Manabung', 'Laki-laki', 'Tahuna', '1980-01-01', 'Kristen', 'Kawin', 'SMA', 'Nelayan', 2500000, 'Kepala Keluarga'],
            ['7171014501840002', 'Maria Manabung', 'Perempuan', 'Tahuna', '1984-01-05', 'Kristen', 'Kawin', 'SMA', 'Ibu Rumah Tangga', 0, 'Istri'],
            ['7171011201120003', 'Rian Manabung', 'Laki-laki', 'Tahuna', '2012-01-12', 'Kristen', 'Belum Kawin', 'SMP', 'Pelajar', 0, 'Anak'],
        ];

        foreach ($penduduk as $row) {
            Penduduk::firstOrCreate(
                ['nik' => $row[0]],
                [
                    'keluarga_id' => $keluarga->id,
                    'nama_lengkap' => $row[1],
                    'jenis_kelamin' => $row[2],
                    'tempat_lahir' => $row[3],
                    'tanggal_lahir' => $row[4],
                    'agama' => $row[5],
                    'status_perkawinan' => $row[6],
                    'pendidikan' => $row[7],
                    'pekerjaan' => $row[8],
                    'penghasilan' => $row[9],
                    'status_dalam_keluarga' => $row[10],
                    'nomor_hp' => null,
                    'status_penduduk' => 'Tetap',
                    'created_by' => $adminKecamatan->id,
                ]
            );
        }
    }
}
