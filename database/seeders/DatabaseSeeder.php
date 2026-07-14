<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
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
        $this->call(RolePermissionSeeder::class);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $kominfoRole = Role::firstOrCreate(['name' => 'admin_kominfo']);
        $opdRole = Role::firstOrCreate(['name' => 'admin_opd']);
        $adminKecamatanRole = Role::firstOrCreate(['name' => 'admin_kecamatan']);
        $adminDesaRole = Role::firstOrCreate(['name' => 'admin_desa']);

        $kabupaten = Kabupaten::firstOrCreate(['nama' => 'Kabupaten Kepulauan Sangihe']);
        $kecamatan = Kecamatan::firstOrCreate(['kabupaten_id' => $kabupaten->id, 'nama' => 'Tahuna']);
        $desa = Desa::firstOrCreate(['kecamatan_id' => $kecamatan->id, 'nama' => 'Soataloara I'], ['kode' => '7171012001']);
        Desa::firstOrCreate(['kecamatan_id' => $kecamatan->id, 'nama' => 'Soataloara II'], ['kode' => '7171012002']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@sipdt.test'],
            ['name' => 'Admin SIPDT', 'password' => Hash::make('password')]
        );
        $admin->assignRole($adminRole);
        $admin->assignRole($superAdminRole);

        $kominfo = User::updateOrCreate(
            ['email' => 'kominfo@sipdt.test'],
            ['name' => 'Admin Kominfo', 'password' => Hash::make('password')]
        );
        $kominfo->syncRoles([$kominfoRole]);

        $adminKecamatan = User::updateOrCreate(
            ['email' => 'admin.kecamatan@sipdt.test'],
            ['name' => 'Admin Kecamatan Tahuna', 'password' => Hash::make('password'), 'kecamatan_id' => $kecamatan->id]
        );
        $adminKecamatan->forceFill(['kecamatan_id' => $kecamatan->id])->save();
        $adminKecamatan->assignRole($adminKecamatanRole);

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

        $opd = \App\Models\Opd::firstOrCreate(['kode' => 'KOMINFO'], ['nama' => 'Dinas Komunikasi dan Informatika']);
        $adminOpd = User::updateOrCreate(
            ['email' => 'admin.opd@sipdt.test'],
            ['name' => 'Admin OPD', 'password' => Hash::make('password'), 'opd_id' => $opd->id]
        );
        $adminOpd->syncRoles([$opdRole]);

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
