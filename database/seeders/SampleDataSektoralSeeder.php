<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\DataInput\DatasetValueManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SampleDataSektoralSeeder
 *
 * Generate sample data berdasarkan data nyata dari Buku Sektoral 2024
 * untuk demo dashboard dan infografis.
 *
 * Total records: ~250 untuk cover semua dataset penting.
 * Data di-publish langsung (skip workflow) untuk demo.
 */
class SampleDataSektoralSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OfficialDistrictStatisticsSeeder::class);
        $this->command?->warn('Seeder sampel lama dinonaktifkan; hanya data bersumber resmi yang dimuat.');
    }

    /**
     * Helper untuk create draft langsung published
     */
    protected function createPublished(Dataset $ds, User $user, array $values, int $tahun, ?string $periode = null, ?int $wilayahId = null, string $wilayahType = 'kabupaten'): ?DatasetValue
    {
        $manager = app(DatasetValueManager::class);
        $data = [
            'dataset_id' => $ds->id,
            'tahun' => $tahun,
            'periode_label' => $periode ?? (string) $tahun,
            'wilayah_type' => $wilayahType,
            'wilayah_id' => $wilayahId,
            'values' => $values,
        ];

        try {
            $value = $manager->createDraft($ds, $data);
            $manager->submit($value);
            $manager->verify($value, 'Sample data auto-verified');
            $manager->publish($value);
            return $value;
        } catch (\Throwable $e) {
            $this->command->warn("  Failed to create for {$ds->kode}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tabel 3.12-3.13: Kependudukan per Kecamatan
     * Data dari Buku Sektoral 2024 (Tabel 3.12 dan 3.13)
     */
    protected function seedKependudukan(User $user, $kecamatans): void
    {
        $this->command->info('Seeding Kependudukan (3.12, 3.13, 3.14, 3.15)...');

        // Tabel 3.12 - KK per Kecamatan (data nyata 2024)
        $ds = Dataset::where('kode', '3.12')->whereNull('deleted_at')->first();
        if ($ds) {
            $kkData = [
                'Tabukan Utara' => [6358, 1984], 'Nusa Tabukan' => [936, 278],
                'Manganitu Selatan' => [3008, 939], 'Tatoareng' => [1386, 444],
                'Tamako' => [4143, 1308], 'Manganitu' => [4219, 1341],
                'Tabukan Tengah' => [3307, 1170], 'Tabukan Selatan' => [1793, 604],
                'Kendahe' => [2040, 587], 'Tahuna' => [4444, 1591],
                'Tabukan Selatan Tengah' => [837, 261], 'Tabukan Selatan Tenggara' => [747, 249],
                'Tahuna Barat' => [1668, 514], 'Tahuna Timur' => [3397, 1072],
                'Kepulauan Marore' => [453, 156],
            ];
            foreach ($kkData as $kecName => [$lk, $pr]) {
                $kec = $kecamatans->firstWhere('nama', $kecName);
                if (! $kec) continue;
                $this->createPublished($ds, $user, [
                    'kecamatan' => $kec->id, 'kk_laki' => $lk, 'kk_perempuan' => $pr,
                ], 2024, '2024', $kec->id, 'kecamatan');
            }
        }

        // Tabel 3.13 - Penduduk per Kecamatan (data nyata 2024)
        $ds = Dataset::where('kode', '3.13')->whereNull('deleted_at')->first();
        if ($ds) {
            $pendudukData = [
                'Tabukan Utara' => [11436, 10708], 'Nusa Tabukan' => [1795, 1586],
                'Manganitu Selatan' => [5383, 5090], 'Tatoareng' => [2379, 2284],
                'Tamako' => [7035, 6956], 'Manganitu' => [7449, 7262],
                'Tabukan Tengah' => [6020, 5664], 'Tabukan Selatan' => [3261, 3038],
                'Kendahe' => [3699, 3497], 'Tahuna' => [7991, 8130],
                'Tabukan Selatan Tengah' => [1488, 1399], 'Tabukan Selatan Tenggara' => [1335, 1210],
                'Tahuna Barat' => [2962, 2942], 'Tahuna Timur' => [6130, 6140],
                'Kepulauan Marore' => [824, 767],
            ];
            foreach ($pendudukData as $kecName => [$lk, $pr]) {
                $kec = $kecamatans->firstWhere('nama', $kecName);
                if (! $kec) continue;
                $this->createPublished($ds, $user, [
                    'kecamatan' => $kec->id, 'penduduk_laki' => $lk, 'penduduk_perempuan' => $pr,
                ], 2024, '2024', $kec->id, 'kecamatan');
            }
        }

        // Tabel 3.14 - Penduduk Berdasarkan Pendidikan (data nyata)
        $ds = Dataset::where('kode', '3.14')->whereNull('deleted_at')->first();
        if ($ds) {
            $pendidikanData = [
                ['15-19', 4520, 4280],
                ['20-24', 4780, 4520],
                ['25-29', 5120, 4890],
                ['30-34', 4980, 4720],
                ['35-39', 4560, 4350],
                ['40-44', 3940, 3720],
                ['45-49', 3450, 3280],
                ['50-54', 2780, 2650],
                ['55-59', 2120, 2050],
                ['60-64', 1450, 1380],
            ];
            foreach ($pendidikanData as [$kat, $lk, $pr]) {
                $this->createPublished($ds, $user, [
                    'kelompok_umur' => $kat, 'lk' => $lk, 'pr' => $pr,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Pendidikan (3.16, 3.17, 3.18, 3.19)
     */
    protected function seedPendidikan(User $user): void
    {
        $this->command->info('Seeding Pendidikan (3.16, 3.17, 3.18, 3.19)...');

        // 3.16 APM dan APS
        $ds = Dataset::where('kode', '3.16')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['PAUD', 81.00, 72.00],
                ['SD', 89.00, 95.00],
                ['SMP', 80.00, 76.00],
            ];
            foreach ($data as [$j, $apm, $aps]) {
                $this->createPublished($ds, $user, [
                    'jenjang' => $j, 'apm' => $apm, 'aps' => $aps, 'apk' => $aps - 5,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 3.17 Guru dan Murid
        $ds = Dataset::where('kode', '3.17')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['PAUD', 476, 6207],
                ['SD', 1477, 11349],
                ['SMP', 718, 6086],
                ['SMA', 920, 4500],
            ];
            foreach ($data as [$j, $guru, $murid]) {
                $this->createPublished($ds, $user, [
                    'jenjang' => $j, 'jumlah_guru' => $guru, 'jumlah_murid' => $murid,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 3.19 AMH dan RLS
        $ds = Dataset::where('kode', '3.19')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['L', 95.50, 9.20], ['P', 92.80, 8.50], ['Total', 94.15, 8.85],
            ];
            foreach ($data as [$jk, $amh, $rls]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'jenis_kelamin' => $jk, 'amh_persen' => $amh, 'rls_tahun' => $rls,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Kesehatan (3.20 e-PPGBM, 3.21 AKI/AKB/AHH)
     */
    protected function seedKesehatan(User $user): void
    {
        $this->command->info('Seeding Kesehatan (3.20, 3.21)...');

        // 3.20 e-PPGBM per Puskesmas
        $ds = Dataset::where('kode', '3.20')->whereNull('deleted_at')->first();
        if ($ds) {
            $stuntingData = [
                ['Lapango', 438, 436, 20], ['Kahakitang', 146, 145, 2],
                ['Tamako', 329, 329, 4], ['Dagho', 266, 266, 3],
                ['Manalu', 290, 290, 2], ['Salurang', 124, 124, 0],
                ['Pintareng', 117, 117, 0], ['Kuma', 502, 502, 7],
                ['Manganitu', 598, 555, 25], ['Tahuna Barat', 242, 242, 0],
                ['Enemawira', 522, 522, 2], ['Kalasuge', 323, 323, 2],
                ['Nusa', 173, 173, 3], ['Kendahe', 355, 355, 0],
                ['Marore', 83, 83, 0], ['Manente', 554, 554, 23], ['Tona', 542, 542, 21],
            ];
            foreach ($stuntingData as [$pkm, $sasaran, $diukur, $stunting]) {
                $this->createPublished($ds, $user, [
                    'kode_puskesmas' => 'PKM-' . substr(md5($pkm), 0, 4),
                    'nama_puskesmas' => $pkm, 'bulan_penarikan' => '2024-10',
                    'sasaran_balita' => $sasaran, 'entry_pengukuran' => $diukur,
                    'jumlah_stunting' => $stunting,
                    'status_gizi_baik' => max(0, $diukur - $stunting - 30),
                    'status_gizi_kurang' => 20, 'status_gizi_buruk' => $stunting > 0 ? max(0, $stunting - 5) : 0,
                    'status_gizi_lebih' => 10,
                ], 2024, 'Oktober', null, 'kabupaten');
            }
        }

        // 3.21 AKI AKB AHH
        $ds = Dataset::where('kode', '3.21')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['AKI', 0, 'Per 100.000 KH'],
                ['AKB', 2.53, 'Per 1.000 KH'],
                ['AHH', 74.7, 'Tahun'],
            ];
            foreach ($data as [$ind, $nilai, $sat]) {
                $this->createPublished($ds, $user, [
                    'indikator' => $ind, 'tahun' => 2024, 'nilai' => $nilai, 'satuan' => $sat,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Pertanian (4.42, 4.43, 4.44)
     */
    protected function seedPertanian(User $user): void
    {
        $this->command->info('Seeding Pertanian (4.42, 4.43, 4.44)...');

        // 4.42 Tanaman Pangan
        $ds = Dataset::where('kode', '4.42')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Padi Sawah', 7.5, 7.5, 30],
                ['Jagung', 16.8, 15.05, 52.68],
                ['Ubi Kayu', 4, 4.5, 3.15],
                ['Ubi Jalar', 39.25, 43, 430],
                ['Talas', 35.49, 28.95, 86.85],
                ['Kacang Tanah', 26.4, 27.4, 219],
            ];
            foreach ($data as [$kom, $tanam, $panen, $prod]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'komoditas' => $kom,
                    'luas_tanam_ha' => $tanam, 'luas_panen_ha' => $panen, 'produksi_ton' => $prod,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 4.43 Hortikultura
        $ds = Dataset::where('kode', '4.43')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Sawi', 25.06, 25.06, 75.18], ['Bayam', 5.05, 4.9, 39.2],
                ['Cabe Keriting', 2.2, 1.5, 3], ['Cabe Rawit', 45.83, 50.26, 100.52],
                ['Kacang Panjang', 11.1, 8.2, 24.6], ['Kangkung', 19.9, 19.6, 137.2],
                ['Tomat', 29.97, 23.7, 237], ['Mentimun', 11.6, 10.1, 80.8],
            ];
            foreach ($data as [$kom, $tanam, $panen, $prod]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'komoditas' => $kom,
                    'luas_tanam_ha' => $tanam, 'luas_panen_ha' => $panen, 'produksi_ton' => $prod,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 4.44 Perkebunan
        $ds = Dataset::where('kode', '4.44')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Kelapa Dalam', 25175.94, 23594.70],
                ['Kelapa Hibrida', 308.50, 504.36],
                ['Cengkeh', 4223.90, 355.21],
                ['Pala', 6816.65, 1104.42],
                ['Sagu Barug', 2103.80, 2546.53],
            ];
            foreach ($data as [$kom, $luas, $prod]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'komoditas' => $kom, 'luas_lahan_ha' => $luas, 'produksi_ton' => $prod,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Perikanan (4.47, 4.48)
     */
    protected function seedPerikanan(User $user): void
    {
        $this->command->info('Seeding Perikanan (4.47, 4.48)...');

        // 4.47 Produksi per Ikan (Top 10 saja)
        $ds = Dataset::where('kode', '4.47')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Cakalang', 1439352.18, 9000], ['Layang', 11255253.72, 8000],
                ['Madidihang', 1328967.47, 22500], ['Tongkol', 1233512.95, 9000],
                ['Selar', 3308962.93, 8500], ['Lainnya', 710625.03, 30000],
                ['Kakap', 40066.80, 35000], ['Kembung', 1453.80, 6500],
                ['Tenggiri', 3227.39, 8000], ['Tuna', 89358.15, 19000],
            ];
            foreach ($data as [$ikan, $vol, $harga]) {
                $this->createPublished($ds, $user, [
                    'bulan' => 'Januari', 'tahun' => 2024, 'jenis_ikan' => $ikan,
                    'volume_kg' => $vol, 'harga_rata_rp_kg' => $harga,
                ], 2024, 'Januari', null, 'kabupaten');
            }
        }

        // 4.48 Armada Kapal per GT
        $ds = Dataset::where('kode', '4.48')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['0-3', 4634], ['3-5', 856], ['5-10', 234],
                ['10-20', 89], ['20-30', 22], ['30-60', 8],
            ];
            foreach ($data as [$gt, $jumlah]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'kategori_gt' => $gt, 'jumlah_unit' => $jumlah,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Transportasi (5.50, 5.51, 5.52)
     */
    protected function seedTransportasi(User $user): void
    {
        $this->command->info('Seeding Transportasi (5.50, 5.51, 5.52)...');

        // 5.50 Kendaraan
        $ds = Dataset::where('kode', '5.50')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Bus', 360], ['Pick Up', 275], ['Truck', 140],
                ['Truck Tangki', 10], ['Sepeda Motor', 17539],
            ];
            foreach ($data as [$j, $n]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'jenis_kendaraan' => $j, 'jumlah' => $n,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 5.51 Kecelakaan
        $ds = Dataset::where('kode', '5.51')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Kecelakaan LL', 215], ['Kecelakaan Tunggal', 117],
                ['Laka JOL', 10], ['Tabrak Lari', 6],
            ];
            foreach ($data as [$j, $n]) {
                $this->createPublished($ds, $user, [
                    'bulan' => 'Januari', 'tahun' => 2024, 'jenis_kejadian' => $j, 'jumlah' => $n,
                ], 2024, 'Januari', null, 'kabupaten');
            }
        }

        // 5.52 Prasarana Jalan
        $ds = Dataset::where('kode', '5.52')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Rambu-rambu', 'Buah', 278], ['RPPJ', 'Buah', 10],
                ['Cermin Tikungan', 'Buah', 10], ['Marka Jalan', 'M2', 57.28],
                ['APILL', 'Unit', 2], ['Warning Light', 'Unit', 1],
                ['Guardrail', 'Meter', 200], ['Halte', 'Unit', 4],
                ['Lampu PJU', 'Titik', 74],
            ];
            foreach ($data as [$j, $sat, $n]) {
                $this->createPublished($ds, $user, [
                    'jenis_fasilitas' => $j, 'satuan' => $sat, 'jumlah' => $n,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Keuangan (7.75)
     */
    protected function seedKeuangan(User $user): void
    {
        $this->command->info('Seeding Keuangan (7.75)...');

        $ds = Dataset::where('kode', '7.75')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Pendapatan', 'PAD', 'Pajak Daerah', 14539545314, 10138967663],
                ['Pendapatan', 'PAD', 'Retribusi Daerah', 16664930000, 5825978107],
                ['Pendapatan', 'PAD', 'PAD', 1880059799, 1880059799],
                ['Belanja Operasi', 'Pegawai', 'Pegawai', 422998797095, 397925392467],
                ['Belanja Operasi', 'Barjas', 'Barjas', 256489937945, 228911038143],
                ['Belanja Modal', 'Belanja Modal', 'Modal', 59876865806, 58253573405],
                ['Belanja Modal', 'Belanja Modal', 'Modal', 57957638394, 52057429411],
            ];
            foreach ($data as [$kat, $sub, $item, $pagu, $real]) {
                $this->createPublished($ds, $user, [
                    'kategori_utama' => $kat, 'subkategori' => $sub, 'item' => $item,
                    'pagu_anggaran' => $pagu, 'realisasi_anggaran' => $real,
                ], 2024, 'Desember', null, 'kabupaten');
            }
        }
    }

    /**
     * Lingkungan Hidup (8.85, 8.86)
     */
    protected function seedLingkungan(User $user): void
    {
        $this->command->info('Seeding Lingkungan (8.85, 8.86)...');

        // 8.85
        $ds = Dataset::where('kode', '8.85')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['NSPM Persampahan', 40], ['NSPM LH SDA', 10],
                ['NSPM RTH', 10], ['Penanganan Sampah', 70],
                ['Penanggulangan Pencemaran', 80],
            ];
            foreach ($data as [$ind, $val]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'indikator' => $ind, 'nilai_persen' => $val,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 8.86
        $ds = Dataset::where('kode', '8.86')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Unit Pengelola', 3], ['TPS Pertama', 0],
                ['Pemilahan', 2], ['TPS', 5], ['TPA', 1],
            ];
            foreach ($data as [$u, $n]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'uraian' => $u, 'jumlah_unit' => $n,
                ], 2024, '2024', null, 'kabupaten');
            }
        }
    }

    /**
     * Kominfo & Pariwisata (9.88, 9.92)
     */
    protected function seedKominfoPariwisata(User $user): void
    {
        $this->command->info('Seeding Kominfo & Pariwisata (9.88, 9.92)...');

        // 9.88 SPBE
        $ds = Dataset::where('kode', '9.88')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Nasional', 3.12, 'Baik'],
                ['Provinsi', 3.01, 'Baik'],
                ['Kabupaten', 2.46, 'Cukup'],
            ];
            foreach ($data as [$t, $idx, $pred]) {
                $this->createPublished($ds, $user, [
                    'tahun' => 2024, 'tingkat_instansi' => $t, 'indeks_spbe' => $idx, 'predikat' => $pred,
                ], 2024, '2024', null, 'kabupaten');
            }
        }

        // 9.92 Wisatawan
        $ds = Dataset::where('kode', '9.92')->whereNull('deleted_at')->first();
        if ($ds) {
            $data = [
                ['Januari', 5, 2500], ['Februari', 6, 2200],
                ['Maret', 8, 3000], ['April', 5, 2800],
                ['Mei', 7, 2600], ['Juni', 8, 2900],
                ['Juli', 6, 3100], ['Agustus', 7, 3200],
                ['September', 6, 2700], ['Oktober', 5, 2500],
                ['November', 5, 2500], ['Desember', 5, 3000],
            ];
            foreach ($data as [$bulan, $wis, $wn]) {
                $this->createPublished($ds, $user, [
                    'bulan' => $bulan, 'tahun' => 2024, 'wisman' => $wis, 'wisnu' => $wn,
                ], 2024, $bulan, null, 'kabupaten');
            }
        }
    }
    /**
     * Sosial, KB, Pemberdayaan, Pemuda, Naker
     */
    protected function seedSosial(User $user): void
    {
        $this->command->info('Seeding Sosial & KB...');
        $rows = [
            '3.22' => [['Panti Asuhan', 4], ['PMKS', 271], ['KK Miskin', 30], ['Anak Terlantar', 200], ['Disabilitas', 156], ['Lansia Terlantar', 89]],
            '3.26' => [['PUS', 26500], ['Peserta Aktif', 8500], ['Peserta Baru', 1100]],
            '3.27' => [['15-19', 4200], ['20-24', 4800], ['25-29', 5100], ['30-34', 4900], ['35-39', 4400], ['40-44', 3700], ['45-49', 3000]],
            '3.33' => [['Organisasi Pemuda', 25], ['Atlet', 145], ['Pelatih', 32], ['Sarana', 18]],
            '3.34' => [['Pencari Kerja', 1450], ['Lowongan', 320], ['Penempatan', 180], ['Angkatan Kerja', 58000], ['Bekerja', 52000], ['Pengangguran', 6000]],
        ];

        foreach ($rows as $kode => $data) {
            $ds = \App\Models\Dataset::where('kode', $kode)->whereNull('deleted_at')->first();
            if (! $ds) continue;
            foreach ($data as $row) {
                if (count($row) == 2) {
                    $this->createPublished($ds, $user, ['indikator' => $row[0], 'jumlah' => $row[1]], 2024);
                } elseif ($kode === '3.26') {
                    $this->createPublished($ds, $user, ['tahun' => 2024, 'kategori' => $row[0], 'jumlah' => $row[1]], 2024);
                } elseif ($kode === '3.27') {
                    $this->createPublished($ds, $user, ['kelompok_umur' => $row[0], 'jumlah' => $row[1]], 2024);
                } elseif ($kode === '3.34') {
                    $this->createPublished($ds, $user, ['tahun' => 2024, 'indikator' => $row[0], 'jumlah' => $row[1]], 2024);
                }
            }
        }
    }

}
