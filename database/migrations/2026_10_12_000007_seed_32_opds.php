<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FASE 1: Seed 32 OPD sesuai Prinsip 14
 *
 * Sebelumnya Opd::KATEGORI_* hanya memiliki 19 kategori.
 * Sekarang di-seed 32 OPD sesuai daftar resmi:
 * 1. Sekretariat Daerah
 * 2. Sekretariat DPRD
 * 3. BKPSDM
 * 4. Disdukcapil
 * 5. Dinas Pendidikan dan Kebudayaan
 * 6. Dinas Kesehatan
 * 7. Dinas Sosial
 * 8. DPPKB
 * 9. DP3A
 * 10. DPMD
 * 11. Dispora
 * 12. Disnaker
 * 13. Dinas Ketahanan Pangan
 * 14. Dinas Pertanian
 * 15. Dinas Perikanan
 * 16. Dinas Perhubungan
 * 17. PUPR
 * 18. Dinas Perpustakaan dan Kearsipan
 * 19. Dinas Perumahan Rakyat, Kawasan Permukiman serta Pertanahan
 * 20. DPMPTSP
 * 21. Dinas Koperasi dan UKM
 * 22. Dinas Perindustrian dan Perdagangan
 * 23. BPKPD
 * 24. Inspektorat
 * 25. Bappelitbang
 * 26. Kesbangpol
 * 27. Badan Pengelola Perbatasan
 * 28. BPBD
 * 29. Satpol PP & Damkar
 * 30. DLH
 * 31. Diskominfo
 * 32. Dinas Pariwisata
 *
 * CATATAN: Migration ini ADDITIVE - jika OPD sudah ada, skip.
 */
return new class () extends Migration {
    public function up(): void
    {
        $opds = [
            ['kode' => 'SETDA', 'nama' => 'Sekretariat Daerah', 'kategori' => 'lainnya', 'singkatan' => 'Setda', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'SETWAN', 'nama' => 'Sekretariat DPRD', 'kategori' => 'lainnya', 'singkatan' => 'Setwan', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'BKPSDM', 'nama' => 'Badan Kepegawaian dan Pengembangan SDM', 'kategori' => 'kepegawaian', 'singkatan' => 'BKPSDM', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DUKCAPIL', 'nama' => 'Dinas Kependudukan dan Pencatatan Sipil', 'kategori' => 'kependudukan', 'singkatan' => 'Disdukcapil', 'aktif' => true, 'is_verifikator' => true],
            ['kode' => 'DISDIK', 'nama' => 'Dinas Pendidikan dan Kebudayaan', 'kategori' => 'pendidikan', 'singkatan' => 'Disdik', 'aktif' => true, 'is_verifikator' => true],
            ['kode' => 'DINKES', 'nama' => 'Dinas Kesehatan', 'kategori' => 'kesehatan', 'singkatan' => 'Dinkes', 'aktif' => true, 'is_verifikator' => true],
            ['kode' => 'DINSOS', 'nama' => 'Dinas Sosial', 'kategori' => 'sosial', 'singkatan' => 'Dinsos', 'aktif' => true, 'is_verifikator' => true],
            ['kode' => 'DPPKB', 'nama' => 'Dinas Pemberdayaan Perempuan dan Keluarga Berencana', 'kategori' => 'sosial', 'singkatan' => 'DPPKB', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DP3A', 'nama' => 'Dinas Pemberdayaan Perempuan dan Perlindungan Anak', 'kategori' => 'sosial', 'singkatan' => 'DP3A', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DPMD', 'nama' => 'Dinas Pemberdayaan Masyarakat Desa', 'kategori' => 'lainnya', 'singkatan' => 'DPMD', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DISPORA', 'nama' => 'Dinas Pemuda dan Olahraga', 'kategori' => 'pemuda', 'singkatan' => 'Dispora', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DISNAKER', 'nama' => 'Dinas Tenaga Kerja', 'kategori' => 'lainnya', 'singkatan' => 'Disnaker', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'KETPANGAN', 'nama' => 'Dinas Ketahanan Pangan', 'kategori' => 'lainnya', 'singkatan' => 'Ketapang', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DISTAN', 'nama' => 'Dinas Pertanian', 'kategori' => 'pertanian', 'singkatan' => 'Distan', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'PERIKANAN', 'nama' => 'Dinas Perikanan', 'kategori' => 'lainnya', 'singkatan' => 'Perikanan', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DISHUB', 'nama' => 'Dinas Perhubungan', 'kategori' => 'hubungan', 'singkatan' => 'Dishub', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'PUPR', 'nama' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'kategori' => 'pu', 'singkatan' => 'PUPR', 'aktif' => true, 'is_verifikator' => true],
            ['kode' => 'PERPUS', 'nama' => 'Dinas Perpustakaan dan Kearsipan', 'kategori' => 'lainnya', 'singkatan' => 'Perpus', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'PERKIM', 'nama' => 'Dinas Perumahan Rakyat, Kawasan Permukiman serta Pertanahan', 'kategori' => 'pu', 'singkatan' => 'Perkim', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DPMPTSP', 'nama' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'kategori' => 'investasi', 'singkatan' => 'DPMPTSP', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'KOPERASI', 'nama' => 'Dinas Koperasi dan UKM', 'kategori' => 'ekonomi', 'singkatan' => 'Koperasi', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DISPERINDAG', 'nama' => 'Dinas Perindustrian dan Perdagangan', 'kategori' => 'perdagangan', 'singkatan' => 'Disperindag', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'BPKPD', 'nama' => 'Badan Pendapatan Keuangan dan Pajak Daerah', 'kategori' => 'keuangan', 'singkatan' => 'BPKPD', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'INSPEKTORAT', 'nama' => 'Inspektorat', 'kategori' => 'lainnya', 'singkatan' => 'Inspektorat', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'BAPPELITBANG', 'nama' => 'Badan Perencanaan Pembangunan, Penelitian dan Pengembangan', 'kategori' => 'perencanaan', 'singkatan' => 'Bappelitbang', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'KESBANGPOL', 'nama' => 'Badan Kesatuan Bangsa dan Politik', 'kategori' => 'lainnya', 'singkatan' => 'Kesbangpol', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'BATAS', 'nama' => 'Badan Pengelola Perbatasan', 'kategori' => 'lainnya', 'singkatan' => 'Batas', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'BPBD', 'nama' => 'Badan Penanggulangan Bencana Daerah', 'kategori' => 'bencana', 'singkatan' => 'BPBD', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'SATPOLPP', 'nama' => 'Satuan Polisi Pamong Praja dan Pemadam Kebakaran', 'kategori' => 'lainnya', 'singkatan' => 'Satpol PP', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DLH', 'nama' => 'Dinas Lingkungan Hidup', 'kategori' => 'lingkungan', 'singkatan' => 'DLH', 'aktif' => true, 'is_verifikator' => false],
            ['kode' => 'DISKOMINFO', 'nama' => 'Dinas Komunikasi dan Informatika', 'kategori' => 'kominfo', 'singkatan' => 'Diskominfo', 'aktif' => true, 'is_verifikator' => true],
            ['kode' => 'PARIWISATA', 'nama' => 'Dinas Pariwisata', 'kategori' => 'pariwisata', 'singkatan' => 'Pariwisata', 'aktif' => true, 'is_verifikator' => false],
        ];

        $now = now();
        foreach ($opds as $opd) {
            $exists = DB::table('opds')->where('kode', $opd['kode'])->exists();
            if (! $exists) {
                $opd['created_at'] = $now;
                $opd['updated_at'] = $now;
                DB::table('opds')->insert($opd);
            }
        }
    }

    public function down(): void
    {
        $newKodes = ['SETDA', 'SETWAN', 'DPPKB', 'DP3A', 'DPMD', 'DISNAKER', 'KETPANGAN', 'PERIKANAN', 'PERPUS', 'PERKIM', 'KOPERASI', 'INSPEKTORAT', 'KESBANGPOL', 'BATAS', 'SATPOLPP'];
        DB::table('opds')->whereIn('kode', $newKodes)->delete();
    }
};
