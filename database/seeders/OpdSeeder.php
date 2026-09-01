<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    public function run(): void
    {
        $opds = [
            // DISKOMINFO - Gateway & Monitoring
            [
                'kode' => 'DISKOMINFO',
                'nama' => 'Dinas Komunikasi dan Informatika',
                'singkatan' => 'DISKOMINFO',
                'deskripsi' => 'Pengelolaan sistem informasi pemerintahan, integrasi data, dan TI',
                'kategori' => Opd::KATEGORI_KOMINFO,
                'alamat' => 'Jl. Merdeka No. 1, Tahuna',
                'telepon' => '(0432) 21001',
                'email' => 'diskominfo@sangihhekab.go.id',
                'is_verifikator' => true,
            ],

            // DINKES - Kesehatan
            [
                'kode' => 'DINKES',
                'nama' => 'Dinas Kesehatan',
                'singkatan' => 'DINKES',
                'deskripsi' => 'Pelayanan kesehatan masyarakat, puskesmas, dan epidemiologi',
                'kategori' => Opd::KATEGORI_KESEHATAN,
                'alamat' => 'Jl. Kesehatan No. 1, Tahuna',
                'telepon' => '(0432) 21002',
                'email' => 'dinkes@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DISDIK - Pendidikan
            [
                'kode' => 'DISDIK',
                'nama' => 'Dinas Pendidikan',
                'singkatan' => 'DISDIK',
                'deskripsi' => 'Pengelolaan pendidikan dasar, menengah, dan non formal',
                'kategori' => Opd::KATEGORI_PENDIDIKAN,
                'alamat' => 'Jl. Pendidikan No. 1, Tahuna',
                'telepon' => '(0432) 21003',
                'email' => 'disdik@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DPUPR - Pekerjaan Umum
            [
                'kode' => 'DPUPR',
                'nama' => 'Dinas Pekerjaan Umum dan Penataan Ruang',
                'singkatan' => 'DPUPR',
                'deskripsi' => 'Pembangunan dan pemeliharaan infrastruktur jalan, jembatan, drainase, air bersih',
                'kategori' => Opd::KATEGORI_PU,
                'alamat' => 'Jl. PU No. 1, Tahuna',
                'telepon' => '(0432) 21004',
                'email' => 'dpupr@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DINSOS - Sosial
            [
                'kode' => 'DINSOS',
                'nama' => 'Dinas Sosial',
                'singkatan' => 'DINSOS',
                'deskripsi' => 'Penanganan masalah kesejahteraan sosial, bansos, dan rehabilitasi',
                'kategori' => Opd::KATEGORI_SOSIAL,
                'alamat' => 'Jl. Sosial No. 1, Tahuna',
                'telepon' => '(0432) 21005',
                'email' => 'dinsos@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DISPERINDAG - Perdagangan
            [
                'kode' => 'DISPERINDAG',
                'nama' => 'Dinas Perindustrian dan Perdagangan',
                'singkatan' => 'DISPERINDAG',
                'deskripsi' => 'Pengelolaan industri, perdagangan, UMKM, dan perlindungan konsumen',
                'kategori' => Opd::KATEGORI_PERDAGANGAN,
                'alamat' => 'Jl. Perdagangan No. 1, Tahuna',
                'telepon' => '(0432) 21006',
                'email' => 'disperindag@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DUKCAPIL - Kependudukan
            [
                'kode' => 'DUKCAPIL',
                'nama' => 'Dinas Kependudukan dan Pencatatan Sipil',
                'singkatan' => 'DUKCAPIL',
                'deskripsi' => 'Pengelolaan data kependudukan, KTP, KK, dan catatan sipil',
                'kategori' => Opd::KATEGORI_KEPENDUDUKAN,
                'alamat' => 'Jl. Kependudukan No. 1, Tahuna',
                'telepon' => '(0432) 21007',
                'email' => 'dukcapil@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DLH - Lingkungan Hidup
            [
                'kode' => 'DLH',
                'nama' => 'Dinas Lingkungan Hidup',
                'singkatan' => 'DLH',
                'deskripsi' => 'Pengelolaan lingkungan hidup, persampahan, dan sanitation',
                'kategori' => Opd::KATEGORI_LINGKUNGAN,
                'alamat' => 'Jl. Lingkungan No. 1, Tahuna',
                'telepon' => '(0432) 21008',
                'email' => 'dlh@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DISTAN - Pertanian & Peternakan
            [
                'kode' => 'DISTAN',
                'nama' => 'Dinas Pertanian dan Peternakan',
                'singkatan' => 'DISTAN',
                'deskripsi' => 'Pengelolaan pertanian, peternakan, perikanan, dan kelautan',
                'kategori' => Opd::KATEGORI_PERTANIAN,
                'alamat' => 'Jl. Pertanian No. 1, Tahuna',
                'telepon' => '(0432) 21009',
                'email' => 'distan@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DISHUB - Perhubungan
            [
                'kode' => 'DISHUB',
                'nama' => 'Dinas Perumahan dan Kawasan Permukiman',
                'singkatan' => 'DISHUB',
                'deskripsi' => 'Pengelolaan transportasi, perhubungan, dan lalu lintas',
                'kategori' => Opd::KATEGORI_HUBUNGAN,
                'alamat' => 'Jl. Transportasi No. 1, Tahuna',
                'telepon' => '(0432) 21010',
                'email' => 'dishub@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // DISTPAR - Pariwisata
            [
                'kode' => 'DISTPAR',
                'nama' => 'Dinas Pariwisata',
                'singkatan' => 'DISTPAR',
                'deskripsi' => 'Pengelolaan pariwisata dan ekonomi kreatif',
                'kategori' => Opd::KATEGORI_PARIWISATA,
                'alamat' => 'Jl. Pariwisata No. 1, Tahuna',
                'telepon' => '(0432) 21011',
                'email' => 'distpar@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // DISPORA - Pemuda dan Olahraga
            [
                'kode' => 'DISPORA',
                'nama' => 'Dinas Pemuda dan Olahraga',
                'singkatan' => 'DISPORA',
                'deskripsi' => 'Pengelolaan pemuda, olahraga, dan prestasi',
                'kategori' => Opd::KATEGORI_PEMUDA,
                'alamat' => 'Jl. Olahraga No. 1, Tahuna',
                'telepon' => '(0432) 21012',
                'email' => 'dispora@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // BKPSDMD - Kepegawaian
            [
                'kode' => 'BKPSDMD',
                'nama' => 'Badan Kepegawaian dan Pengembangan SDM',
                'singkatan' => 'BKPSDMD',
                'deskripsi' => 'Pengelolaan kepegawaian dan pengembangan sumber daya manusia',
                'kategori' => Opd::KATEGORI_KEPEGAWAIAN,
                'alamat' => 'Jl. Pegawai No. 1, Tahuna',
                'telepon' => '(0432) 21013',
                'email' => 'bkpsdmd@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // BAPPEDA - Perencanaan
            [
                'kode' => 'BAPPEDA',
                'nama' => 'Badan Perencanaan Pembangunan Daerah',
                'singkatan' => 'BAPPEDA',
                'deskripsi' => 'Perencanaan pembangunan dan pengelolaan data statistik',
                'kategori' => Opd::KATEGORI_PERENCANAAN,
                'alamat' => 'Jl. Perencanaan No. 1, Tahuna',
                'telepon' => '(0432) 21014',
                'email' => 'bappeda@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // BAPENDA - Keuangan
            [
                'kode' => 'BAPENDA',
                'nama' => 'Badan Pendapatan Daerah',
                'singkatan' => 'BAPENDA',
                'deskripsi' => 'Pengelolaan pendapatan daerah dan pajak',
                'kategori' => Opd::KATEGORI_KEUANGAN,
                'alamat' => 'Jl. Keuangan No. 1, Tahuna',
                'telepon' => '(0432) 21015',
                'email' => 'bapenda@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // BPBD - Bencana
            [
                'kode' => 'BPBD',
                'nama' => 'Badan Penanggulangan Bencana Daerah',
                'singkatan' => 'BPBD',
                'deskripsi' => 'Penanggulangan bencana dan kebencanaan',
                'kategori' => Opd::KATEGORI_BENCANA,
                'alamat' => 'Jl. Bencana No. 1, Tahuna',
                'telepon' => '(0432) 21016',
                'email' => 'bpbd@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => true,
            ],

            // DPMPTSP - Investasi
            [
                'kode' => 'DPMPTSP',
                'nama' => 'Dinas Penanaman Modal dan PTSP',
                'singkatan' => 'DPMPTSP',
                'deskripsi' => 'Pelayanan perizinan dan penanaman modal',
                'kategori' => Opd::KATEGORI_INVESTASI,
                'alamat' => 'Jl. PTSP No. 1, Tahuna',
                'telepon' => '(0432) 21017',
                'email' => 'dpmptsp@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],

            // DISTAMAN - Perkebunan
            [
                'kode' => 'DISTAMAN',
                'nama' => 'Dinas Perkebunan dan Pertambangan',
                'singkatan' => 'DISTAMAN',
                'deskripsi' => 'Pengelolaan perkebunan dan pertambangan',
                'kategori' => Opd::KATEGORI_PERKEBUNAN,
                'alamat' => 'Jl. Perkebunan No. 1, Tahuna',
                'telepon' => '(0432) 21018',
                'email' => 'distaman@sangihhekab.go.id',
                'kepala_dinas' => '',
                'nip_kepala_dinas' => '',
                'is_verifikator' => false,
            ],
        ];

        foreach ($opds as $opd) {
            Opd::updateOrCreate(
                ['kode' => $opd['kode']],
                $opd + ['aktif' => true]
            );
        }
    }
}
