<?php

namespace Database\Seeders;

use App\Models\ForumSatuData;
use App\Models\ForumStruktur;
use App\Models\Opd;
use Illuminate\Database\Seeder;

/**
 * SDI: Seed Struktur Forum Satu Data Kabupaten Kepulauan Sangihe
 *
 * Mengacu Perpres 39/2019 dan standar Perbup Satu Data Kabupaten.
 * Silakan sesuaikan no_sk, nama_pejabat, dan nip sesuai SK Bupati asli.
 */
class ForumSatuDataSangiheSeeder extends Seeder
{
    public function run(): void
    {
        $forum = ForumSatuData::updateOrCreate(
            ['tentang' => 'Forum Satu Data Kabupaten Kepulauan Sangihe'],
            [
                'no_sk' => '188/XXX/Tahun-2026',
                'tanggal_sk' => '2026-02-15',
                'berlaku_mulai' => '2026-01-01',
                'berlaku_sampai' => '2029-12-31',
                'keterangan' => 'Sesuai Perpres 39/2019 ttg Satu Data Indonesia, setiap kabupaten membentuk Forum Satu Data Kabupaten (FSDK) untuk mengoordinasikan pengelolaan data.',
                'is_aktif' => true,
            ]
        );

        // Struktur Peran FSDK
        $roles = [
            // ---- TINGKAT PEMBINA & PENGARAH ----
            ['peran' => 'pembina', 'nama' => 'BUPATI KEPULAUAN SANGIHE', 'jabatan' => 'Bupati'],
            ['peran' => 'pengarah', 'nama' => 'WAKIL BUPATI KEPULAUAN SANGIHE', 'jabatan' => 'Wakil Bupati'],
            ['peran' => 'penanggung_jawab', 'nama' => 'SEKRETARIS DAERAH', 'jabatan' => 'Sekda Kab. Sangihe', 'opd' => 'SETDA'],

            // ---- FORUM INTI ----
            ['peran' => 'ketua', 'nama' => 'KEPALA BAPPELITBANG', 'jabatan' => 'Ka Bappelitbang', 'opd' => 'BAPPELITBANG'],
            ['peran' => 'wakil_ketua', 'nama' => 'KEPALA BAGIAN PEMERINTAHAN', 'jabatan' => 'Kabag Pemerintahan', 'opd' => 'SETDA'],
            ['peran' => 'koordinator', 'nama' => 'KEPALA DINAS KOMINFO', 'jabatan' => 'Ka Diskominfo', 'opd' => 'DISKOMINFO'],
            ['peran' => 'wali_data', 'nama' => 'KEPALA BIDANG PERSANDIAN & STATISTIK', 'jabatan' => 'Kabid Persandian & Statistik', 'opd' => 'DISKOMINFO'],
            ['peran' => 'koordinator_statistik', 'nama' => 'KEPALA BIDANG STATISTIK', 'jabatan' => 'Kabid Statistik', 'opd' => 'DISKOMINFO'],
            ['peran' => 'sekretariat', 'nama' => 'SEKRETARIAT FSDK', 'jabatan' => 'Sekretariat Forum', 'opd' => 'BAPPELITBANG'],

            // ---- ANGGOTA (PRODUSEN DATA) - Semua 32 OPD ----
            ...$this->anggotaOpdList(),
        ];

        // Cache OPD ids by kode (untuk performance)
        $opdIds = Opd::pluck('id', 'kode')->toArray();

        foreach ($roles as $i => $role) {
            $opdId = isset($role['opd']) ? ($opdIds[$role['opd']] ?? null) : null;

            ForumStruktur::updateOrCreate(
                [
                    'forum_satu_data_id' => $forum->id,
                    'peran' => $role['peran'],
                    'opd_id' => $opdId,
                ],
                [
                    'nama_pejabat' => $role['nama'],
                    'jabatan_asal' => $role['jabatan'],
                    'is_aktif' => true,
                    'urutan' => $i,
                ]
            );
        }
    }

    private function anggotaOpdList(): array
    {
        $opdCodes = [
            'DISKOMINFO' => 'Walidata & Koordinator',
            'DINKES' => 'Dinas Kesehatan',
            'DISDIK' => 'Dinas Pendidikan',
            'DPUPR' => 'Dinas Pekerjaan Umum',
            'DINSOS' => 'Dinas Sosial',
            'DISPERINDAG' => 'Dinas Perindustrian & Perdagangan',
            'DUKCAPIL' => 'Dinas Kependudukan',
            'DLH' => 'Dinas Lingkungan Hidup',
            'DISTAN' => 'Dinas Pertanian',
            'DISHUB' => 'Dinas Perhubungan',
            'PARIWISATA' => 'Dinas Pariwisata',
            'DISPORA' => 'Dinas Pemuda & Olahraga',
            'BKPSDM' => 'Badan Kepegawaian',
            'BAPPELITBANG' => 'Badan Perencanaan',
            'BPKPD' => 'Badan Pendapatan',
            'BPBD' => 'Badan Penanggulangan Bencana',
            'DPMPTSP' => 'Dinas Penanaman Modal',
            'DISTAMAN' => 'Dinas Perkebunan & Tambang',
            'DPPKB' => 'Dinas KB',
            'DP3A' => 'Dinas Pemberdayaan Perempuan',
            'DPMD' => 'Dinas Pemberdayaan Desa',
            'PERIKANAN' => 'Dinas Perikanan',
            'BATAS' => 'Badan Pengelola Perbatasan',
            'PERKIM' => 'Dinas Perumahan & Permukiman',
            'KOPERASI' => 'Dinas Koperasi & UKM',
            'KETPANGAN' => 'Dinas Ketahanan Pangan',
            'DISHUBDARAT' => 'Dinas Perhubungan Darat',
            'DISNAKER' => 'Dinas Tenaga Kerja',
            'PERPUS' => 'Dinas Perpustakaan',
            'KESBANGPOL' => 'Badan Kesbangpol',
            'INSPEKTORAT' => 'Inspektorat',
            'SATPOLPP' => 'Satpol PP & Damkar',
        ];

        $result = [];
        foreach ($opdCodes as $kode => $jabatan) {
            $result[] = [
                'peran' => 'anggota',
                'nama' => 'KEPALA '.strtoupper($jabatan),
                'opd' => $kode,
                'jabatan' => 'Kepala '.strtoupper($jabatan),
            ];
        }
        return $result;
    }
}
