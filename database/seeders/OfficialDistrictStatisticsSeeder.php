<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Verified district-level population and household statistics from the 2024 sectoral book. */
class OfficialDistrictStatisticsSeeder extends Seeder
{
    private const SOURCE = 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024; sumber tabel: Dinas Kependudukan dan Pencatatan Sipil Daerah.';

    public function run(): void
    {
        $datasets = Dataset::query()->whereIn('kode', ['2.8', '2.9', '2.10', '2.11', '3.12', '3.13', '3.18', '3.19', '3.22', '3.26', '3.33', '3.34', '3.35', '3.36', '5.59', '5.60', '6.64', '6.65', '6.66', '6.68', '6.71'])->whereNull('deleted_at')->get()->keyBy('kode');
        $this->ensureDistrictFields($datasets);
        $districts = Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->get()->keyBy('nama');
        $this->assertDatasetFields($datasets);

        if ($districts->count() !== 15) {
            throw new RuntimeException('Data resmi ini mensyaratkan tepat 15 kecamatan aktif; tidak ada data yang diubah.');
        }

        $households = [
            'Tabukan Utara' => [6358, 1984, 8342], 'Nusa Tabukan' => [936, 278, 1214],
            'Manganitu Selatan' => [3008, 939, 3947], 'Tatoareng' => [1386, 444, 1830],
            'Tamako' => [4143, 1308, 5451], 'Manganitu' => [4219, 1341, 5560],
            'Tabukan Tengah' => [3307, 1170, 4477], 'Tabukan Selatan' => [1793, 604, 2397],
            'Kendahe' => [2040, 587, 2627], 'Tahuna' => [4444, 1591, 6038],
            'Tabukan Selatan Tengah' => [837, 261, 1098], 'Tabukan Selatan Tenggara' => [747, 249, 996],
            'Tahuna Barat' => [1668, 514, 2182], 'Tahuna Timur' => [3397, 1072, 4469],
            'Kepulauan Marore' => [453, 156, 609],
        ];
        $population = [
            'Tabukan Utara' => [11436, 10708, 22144], 'Nusa Tabukan' => [1795, 1586, 3381],
            'Manganitu Selatan' => [5383, 5090, 10473], 'Tatoareng' => [2379, 2284, 4663],
            'Tamako' => [7035, 6956, 13991], 'Manganitu' => [7449, 7262, 14711],
            'Tabukan Tengah' => [6020, 5664, 11684], 'Tabukan Selatan' => [3261, 3038, 6299],
            'Kendahe' => [3699, 3497, 7196], 'Tahuna' => [7991, 8130, 16121],
            'Tabukan Selatan Tengah' => [1488, 1399, 2887], 'Tabukan Selatan Tenggara' => [1335, 1210, 2545],
            'Tahuna Barat' => [2962, 2942, 5904], 'Tahuna Timur' => [6130, 6140, 12270],
            'Kepulauan Marore' => [824, 767, 1591],
        ];

        $this->assertSourceTotals($households, [38736, 12498, 51237], 'KK');
        $this->assertSourceTotals($population, [69187, 66673, 135860], 'penduduk');
        $missing = collect(array_keys($households))->diff($districts->keys());
        if ($missing->isNotEmpty()) {
            throw new RuntimeException('Kecamatan berikut tidak ditemukan; tidak ada data yang diubah: ' . $missing->implode(', '));
        }

        DB::transaction(function () use ($datasets, $districts, $households, $population): void {
            foreach ([
                '3.12' => [$households, ['kk_laki', 'kk_perempuan', 'total']],
                '3.13' => [$population, ['penduduk_laki', 'penduduk_perempuan', 'total']],
            ] as $code => [$rows, $fields]) {
                $dataset = $datasets->get($code);
                foreach ($rows as $name => [$male, $female, $reportedTotal]) {
                    $district = $districts->get($name);
                    $values = [
                        'kecamatan' => $district->id,
                        $fields[0] => $male,
                        $fields[1] => $female,
                        $fields[2] => $reportedTotal,
                    ];
                    $record = DatasetValue::withTrashed()->firstOrNew([
                        'dataset_id' => $dataset->id,
                        'tahun' => 2024,
                        'wilayah_type' => Wilayah::TIPE_KECAMATAN,
                        'wilayah_id' => $district->id,
                    ]);
                    $record->periode_label = '2024';
                    $record->values = $values;
                    $record->status = DatasetValue::STATUS_PUBLISHED;
                    $record->status_verifikasi = DatasetValue::STATUS_PUBLISHED;
                    $record->published_at ??= now();
                    $record->notes = self::SOURCE . ($code === '3.12'
                        ? ' Tabel 3.12 mencetak total agregat 51.234 KK, sementara jumlah angka total per kecamatan yang tercetak adalah 51.237 KK; nilai kecamatan mengikuti angka rinci dalam tabel.'
                        : ' Tabel 3.13, jumlah penduduk per kecamatan tahun 2024.');
                    $record->deleted_at = null;
                    $record->save();
                }
            }

            $this->seedRows($datasets->get('2.8'), 'golongan', [
                ['golongan' => 'I', 'tahun' => 2024, 'jumlah' => 6],
                ['golongan' => 'II', 'tahun' => 2024, 'jumlah' => 491],
                ['golongan' => 'III', 'tahun' => 2024, 'jumlah' => 2119],
                ['golongan' => 'IV', 'tahun' => 2024, 'jumlah' => 633],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 2.8; sumber: Badan Kepegawaian dan Pengembangan SDM Daerah.');

            $this->seedRows($datasets->get('2.9'), 'eselon', [
                ['eselon' => 'II', 'tahun' => 2024, 'jumlah' => 28],
                ['eselon' => 'III', 'tahun' => 2024, 'jumlah' => 166],
                ['eselon' => 'IV', 'tahun' => 2024, 'jumlah' => 220],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 2.9; sumber: Badan Kepegawaian dan Pengembangan SDM Daerah.');

            $this->seedRows($datasets->get('2.10'), 'jabatan', [
                ['jabatan' => 'Struktural', 'tahun' => 2024, 'jumlah' => 414],
                ['jabatan' => 'Fungsional', 'tahun' => 2024, 'jumlah' => 2735],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 2.10; sumber: Badan Kepegawaian dan Pengembangan SDM Daerah.');

            $this->seedRows($datasets->get('2.11'), 'pendidikan', [
                ['pendidikan' => 'SD', 'tahun' => 2024, 'jumlah' => 2],
                ['pendidikan' => 'SMP', 'tahun' => 2024, 'jumlah' => 7],
                ['pendidikan' => 'SMA', 'tahun' => 2024, 'jumlah' => 547],
                ['pendidikan' => 'D1', 'tahun' => 2024, 'jumlah' => 9],
                ['pendidikan' => 'D2', 'tahun' => 2024, 'jumlah' => 71],
                ['pendidikan' => 'D3', 'tahun' => 2024, 'jumlah' => 605],
                ['pendidikan' => 'S1/D4', 'tahun' => 2024, 'jumlah' => 1804],
                ['pendidikan' => 'S2', 'tahun' => 2024, 'jumlah' => 104],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 2.11; sumber: Badan Kepegawaian dan Pengembangan SDM Daerah.');

            $this->seedRows($datasets->get('5.60'), 'jenis', [
                ['jenis' => 'Perpustakaan daerah', 'jumlah' => 1],
                ['jenis' => 'Perpustakaan desa', 'jumlah' => 9],
                ['jenis' => 'Perpustakaan kelurahan', 'jumlah' => 9],
                ['jenis' => 'Perpustakaan sekolah', 'jumlah' => 229],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 5.60; sumber: Dinas Perpustakaan dan Kearsipan Daerah.');

            $this->seedRows($datasets->get('6.64'), 'jenis_usaha', [
                ['jenis_usaha' => 'Mikro', 'jumlah_usaha' => 374, 'nilai_investasi' => 32014240000],
                ['jenis_usaha' => 'Kecil', 'jumlah_usaha' => 286, 'nilai_investasi' => 2815211800875],
                ['jenis_usaha' => 'Menengah', 'jumlah_usaha' => 12, 'nilai_investasi' => 20500000000],
                ['jenis_usaha' => 'Besar', 'jumlah_usaha' => 47, 'nilai_investasi' => 140272855689],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 6.64; sumber: Dinas Penanaman Modal dan PTSP Daerah.');

            $licenses = [
                ['Surat Izin Perdagangan Minuman Beralkohol', 1], ['Surat Izin Usaha Perdagangan', 108],
                ['Pelayanan Tanda Daftar Perusahaan', 131], ['Penerbitan Tanda Daftar Industri', 3],
                ['Tanda Daftar Gudang', 5], ['Surat Izin Tempat Usaha', 1184],
                ['Izin Tempat Penjualan Minuman Beralkohol', 1], ['Izin Trayek', 243],
                ['Surat Izin Toko Obat', 2], ['Surat Izin Apotek', 2],
                ['Surat Izin Praktik Apoteker', 9], ['Surat Izin Kerja Bidan', 52],
                ['Surat Izin Praktik Bidan', 52], ['Surat Izin Praktik Tenaga Teknis Kefarmasian', 8],
                ['Surat Izin Kerja Tenaga Teknis Kefarmasian', 1], ['Surat Izin Praktik Perawat', 302],
                ['Surat Izin Operasional Klinik', 1], ['Surat Izin Praktik Refraksionis Optisien', 1],
                ['Surat Izin Kerja Refraksionis Optisien', 1], ['Surat Izin Industri Rumah Tangga', 7],
                ['Surat Izin Praktik Tenaga Promosi Kesehatan dan Ilmu Perilaku', 1],
                ['Surat Izin Praktik Terapis Gigi dan Mulut', 4], ['Surat Izin Praktik Ahli Laboratorium Medik', 9],
                ['Surat Izin Operasional Rumah Sakit', 1], ['Surat Izin Praktik Tenaga Gizi', 10],
                ['Surat Izin Pendirian dan Operasional Lembaga PAUD/TK', 22],
                ['Surat Izin Kerja Tenaga Gizi', 11], ['Surat Izin Kerja Tenaga Sanitarian', 3],
                ['Surat Izin Operasional Puskesmas', 2], ['Surat Izin Praktik Tenaga Kesehatan Tradisional Keterampilan', 1],
            ];
            $this->seedRows($datasets->get('6.65'), 'jenis_izin', array_map(fn (array $row) => [
                'jenis_izin' => $row[0], 'jumlah_pemohon' => $row[1],
            ], $licenses), 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 6.65; sumber: Dinas Penanaman Modal dan PTSP Daerah.');

            $this->seedRows($datasets->get('6.66'), 'indikator', [
                ['tahun' => 2024, 'indikator' => 'Lembaga Koperasi', 'jumlah' => 172],
                ['tahun' => 2024, 'indikator' => 'Koperasi Aktif', 'jumlah' => 111],
                ['tahun' => 2024, 'indikator' => 'UMKM', 'jumlah' => 11736],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 6.66; sumber: Dinas Koperasi dan UKM Daerah.');

            $this->seedRows($datasets->get('3.18'), 'nama_sarana', [
                ['kategori' => 'Kesenian Tradisional', 'nama_sarana' => 'Elemen adat budaya yang dilestarikan', 'jumlah' => 6],
                ['kategori' => 'Sarana Pendukung', 'nama_sarana' => 'Lembaga pembina tingkat kabupaten', 'jumlah' => 1],
                ['kategori' => 'Sarana Pendukung', 'nama_sarana' => 'Situs sejarah lokal daerah', 'jumlah' => 15],
                ['kategori' => 'Sarana Pendukung', 'nama_sarana' => 'Benda cagar budaya', 'jumlah' => 2],
                ['kategori' => 'Grup Kesenian', 'nama_sarana' => 'Grup kesenian', 'jumlah' => 35],
                ['kategori' => 'Sarana Pendukung', 'nama_sarana' => 'Sarana penyelenggaraan seni dan budaya', 'jumlah' => 6],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.18; sumber: Dinas Pendidikan dan Kebudayaan Daerah.');

            $this->seedRows($datasets->get('3.19'), 'jenis_kelamin', [
                ['tahun' => 2024, 'jenis_kelamin' => 'Total', 'amh_persen' => 99.55, 'rls_tahun' => 7.91],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.19; sumber: Dinas Pendidikan dan Kebudayaan Daerah. Angka merupakan agregat kabupaten, bukan rincian jenis kelamin.');

            $this->seedRows($datasets->get('3.22'), 'indikator', [
                ['indikator' => 'Panti Asuhan', 'jumlah' => 4],
                ['indikator' => 'PMKS', 'jumlah' => 271],
                ['indikator' => 'KK Miskin', 'jumlah' => 30],
                ['indikator' => 'Anak Terlantar', 'jumlah' => 200],
                ['indikator' => 'Disabilitas', 'jumlah' => 3],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.22; sumber: Dinas Sosial Daerah.');

            $pusRows = [
                'Tabukan Utara' => [2673, 1511], 'Nusa Tabukan' => [432, 278],
                'Manganitu Selatan' => [1578, 1128], 'Tatoareng' => [483, 263],
                'Tamako' => [2044, 1368], 'Manganitu' => [1936, 1343],
                'Tabukan Tengah' => [1596, 1025], 'Tabukan Selatan' => [873, 594],
                'Kendahe' => [916, 386], 'Tahuna' => [2110, 1471],
                'Tabukan Selatan Tengah' => [366, 279], 'Tabukan Selatan Tenggara' => [326, 224],
                'Tahuna Barat' => [796, 505], 'Tahuna Timur' => [1561, 938],
                'Kepulauan Marore' => [219, 133],
            ];
            $this->assertSourceTotals(array_map(fn ($row) => [$row[0], $row[1], $row[0] + $row[1]], $pusRows), [17909, 11446, 29355], 'PUS/KB aktif');
            $this->seedDistrictRows($datasets->get('3.26'), $districts, collect($pusRows)->flatMap(fn ($row, $name) => [
                ['kecamatan' => $districts->get($name)->id, 'tahun' => 2024, 'kategori' => 'PUS', 'jumlah' => $row[0]],
                ['kecamatan' => $districts->get($name)->id, 'tahun' => 2024, 'kategori' => 'Peserta Aktif', 'jumlah' => $row[1]],
            ])->all(), 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.26; sumber: Dinas Pengendalian Penduduk dan KB Daerah.');

            $this->seedRows($datasets->get('3.33'), 'indikator', [
                ['indikator' => 'Organisasi Pemuda', 'tahun' => 2024, 'jumlah' => 285],
                ['indikator' => 'Organisasi Olahraga', 'tahun' => 2024, 'jumlah' => 27],
                ['indikator' => 'Kegiatan Kepemudaan', 'tahun' => 2024, 'jumlah' => 570],
                ['indikator' => 'Kegiatan Olahraga', 'tahun' => 2024, 'jumlah' => 55],
                ['indikator' => 'Pelatih Aktif', 'tahun' => 2024, 'jumlah' => 86],
                ['indikator' => 'Klub Olahraga', 'tahun' => 2024, 'jumlah' => 206],
                ['indikator' => 'Cabang Olahraga', 'tahun' => 2024, 'jumlah' => 29],
                ['indikator' => 'Workshop Kepelatihan', 'tahun' => 2024, 'jumlah' => 6],
                ['indikator' => 'Kompetisi Nasional', 'tahun' => 2024, 'jumlah' => 4],
                ['indikator' => 'Kompetisi Regional', 'tahun' => 2024, 'jumlah' => 20],
                ['indikator' => 'Lapangan Sepak Bola', 'tahun' => 2024, 'jumlah' => 30],
                ['indikator' => 'Gedung Olahraga', 'tahun' => 2024, 'jumlah' => 5],
                ['indikator' => 'Medali Regional', 'tahun' => 2024, 'jumlah' => 107],
                ['indikator' => 'Medali Nasional', 'tahun' => 2024, 'jumlah' => 3],
                ['indikator' => 'Pemuda Terlatih', 'tahun' => 2024, 'jumlah' => 94],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.33; sumber: Dinas Pemuda dan Olahraga Daerah.');

            $this->seedRows($datasets->get('3.34'), 'indikator', [
                ['tahun' => 2024, 'indikator' => 'Pencari Kerja', 'jumlah' => 297],
                ['tahun' => 2024, 'indikator' => 'Pelatihan Kompetensi', 'jumlah' => 120],
                ['tahun' => 2024, 'indikator' => 'Instruktur Bersertifikat', 'jumlah' => 2],
                ['tahun' => 2024, 'indikator' => 'Penempatan', 'jumlah' => 22],
            ], 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.34; sumber: Dinas Tenaga Kerja Daerah. Hanya indikator dengan jumlah orang yang disimpan.');

            $tpt = [
                'Tabukan Utara' => [15447, 330, 2.26], 'Tahuna Timur' => [8506, 206, 2.42],
                'Tahuna' => [11411, 326, 2.85], 'Tahuna Barat' => [4110, 290, 7.05],
                'Tamako' => [9479, 262, 2.76], 'Manganitu' => [10159, 188, 1.85],
                'Tabukan Tengah' => [8094, 192, 2.37], 'Kendahe' => [4867, 132, 2.71],
                'Tabukan Selatan' => [4311, 102, 2.36], 'Manganitu Selatan' => [7215, 88, 1.21],
                'Tabukan Selatan Tengah' => [1913, 67, 3.56], 'Tatoareng' => [3364, 70, 2.08],
                'Tabukan Selatan Tenggara' => [1788, 50, 2.80], 'Nusa Tabukan' => [2406, 19, 0.78],
                'Kepulauan Marore' => [1170, 14, 1.19],
            ];
            $tpak = [
                'Tabukan Utara' => [15447, 8150, 52.76], 'Tahuna Timur' => [8506, 6173, 72.57],
                'Tahuna' => [11411, 8650, 75.80], 'Tahuna Barat' => [4110, 3170, 77.13],
                'Tamako' => [9479, 6852, 72.29], 'Manganitu' => [10159, 6176, 60.79],
                'Tabukan Tengah' => [8094, 5258, 64.96], 'Kendahe' => [4867, 2470, 50.85],
                'Tabukan Selatan' => [4311, 2546, 59.06], 'Manganitu Selatan' => [7215, 4310, 59.74],
                'Tabukan Selatan Tengah' => [1913, 1200, 62.73], 'Tatoareng' => [3364, 1861, 55.32],
                'Tabukan Selatan Tenggara' => [1788, 985, 55.09], 'Nusa Tabukan' => [2406, 1083, 45.01],
                'Kepulauan Marore' => [1170, 732, 62.56],
            ];
            $this->seedDistrictRows($datasets->get('3.35'), $districts, collect($tpt)->map(fn ($row, $name) => [
                'kecamatan' => $districts->get($name)->id, 'jk' => 'Total', 'usia_kerja' => $row[0],
                'jumlah_pengangguran' => $row[1], 'tpt' => $row[2],
            ])->values()->all(), 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.35; sumber: Dinas Tenaga Kerja Daerah.');
            $this->seedDistrictRows($datasets->get('3.36'), $districts, collect($tpak)->map(fn ($row, $name) => [
                'kecamatan' => $districts->get($name)->id, 'jk' => 'Total', 'usia_kerja' => $row[0],
                'angkatan_kerja' => $row[1], 'tpak' => $row[2],
            ])->values()->all(), 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 3.36; sumber: Dinas Tenaga Kerja Daerah.');

            $roads = [
                ['kategori' => 'Jalan Kabupaten', 'uraian' => 'Panjang jalan kabupaten (km)', 'nilai' => 379.97],
                ['kategori' => 'Jalan Provinsi', 'uraian' => 'Panjang jalan provinsi (km)', 'nilai' => 58.28],
                ['kategori' => 'Jalan Nasional', 'uraian' => 'Panjang jalan nasional (km)', 'nilai' => 171.81],
                ['kategori' => 'Konstruksi Permukaan', 'uraian' => 'Aspal/pavement (km)', 'nilai' => 293.60],
                ['kategori' => 'Konstruksi Permukaan', 'uraian' => 'Penetrasi/kerikil (km)', 'nilai' => 2.59],
                ['kategori' => 'Konstruksi Permukaan', 'uraian' => 'Beton (km)', 'nilai' => 53.24],
                ['kategori' => 'Konstruksi Permukaan', 'uraian' => 'Tanah (km)', 'nilai' => 30.54],
                ['kategori' => 'Kualifikasi Kondisi', 'uraian' => 'Jalan kondisi baik (km)', 'nilai' => 190.81],
                ['kategori' => 'Kualifikasi Kondisi', 'uraian' => 'Jalan rusak sedang (km)', 'nilai' => 58.47],
                ['kategori' => 'Kualifikasi Kondisi', 'uraian' => 'Jalan rusak ringan (km)', 'nilai' => 31.75],
                ['kategori' => 'Kualifikasi Kondisi', 'uraian' => 'Jalan rusak berat (km)', 'nilai' => 98.94],
                ['kategori' => 'Jembatan', 'uraian' => 'Jumlah jembatan (unit)', 'nilai' => 170],
                ['kategori' => 'Jembatan', 'uraian' => 'Panjang jembatan (m)', 'nilai' => 1714],
            ];
            $this->seedRows($datasets->get('5.59'), 'uraian', $roads, 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 5.59; sumber: Dinas PUPR Daerah. Data ini agregat kabupaten; PDF tidak memberi koordinat ruas per kecamatan.');

            $industry = [
                ['Aneka Kue', 165, 350], ['Pembuatan Selai', 30, 39], ['Salon', 27, 44],
                ['Makanan dan Minuman', 43, 86], ['Olahan Minyak', 25, 113], ['Batu Bata', 12, 20],
                ['Mebel Kayu', 117, 217], ['Pakaian Jadi', 25, 47], ['Tukang Gigi', 5, 5],
                ['Kerajinan Alat Seni', 13, 32], ['Pande Besi', 78, 107], ['Mebel Bambu', 16, 119],
                ['Kembang', 7, 12], ['Kerajinan Anyaman', 42, 60], ['Fotokopi dan Percetakan', 12, 21],
                ['Penjahit', 31, 40], ['Pertukangan', 61, 82], ['Perbengkelan dan Reparasi', 98, 246],
                ['Perikanan', 21, 49], ['Depot Air Isi Ulang', 18, 24], ['Kerajinan Sulaman Kerawang', 179, 179],
                ['Reparasi Elektronik dan Jam', 24, 53], ['Tukang Periuk', 14, 22], ['Studio Rekaman', 1, 4],
            ];
            $this->seedRows($datasets->get('6.68'), 'jenis', array_map(fn (array $row) => [
                'jenis' => $row[0], 'jumlah_unit' => $row[1], 'tenaga_kerja' => $row[2],
            ], $industry), 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 6.68; sumber: Dinas Perindustrian dan Perdagangan Daerah.');

            $markets = [
                'Tahuna Timur' => [1, 1], 'Tahuna' => [4, 4], 'Tahuna Barat' => [1, 1],
                'Kendahe' => [2, 2], 'Tabukan Utara' => [3, 3], 'Tabukan Selatan Tenggara' => [1, 1],
                'Tabukan Selatan Tengah' => [1, 2], 'Tabukan Selatan' => [1, 1], 'Tabukan Tengah' => [4, 4],
                'Manganitu' => [3, 3], 'Tamako' => [2, 2], 'Manganitu Selatan' => [6, 7],
                'Nusa Tabukan' => [0, 0], 'Kepulauan Marore' => [0, 0], 'Tatoareng' => [0, 0],
            ];
            $this->seedDistrictRows($datasets->get('6.71'), $districts, collect($markets)->map(fn ($row, $name) => [
                'kecamatan' => $districts->get($name)->id, 'jumlah_pasar' => $row[0], 'jumlah_bangunan' => $row[1],
            ])->values()->all(), 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024, tabel 6.71; sumber: Dinas Perindustrian dan Perdagangan Daerah.');

            // Hide only records explicitly labeled as generated samples; preserve audit history.
            DatasetValue::withTrashed()->where(fn ($query) => $query
                ->where('notes', 'like', '[DEMO-INFOGRAFIS]%')
                ->orWhereIn('notes', ['Sample data auto-verified', 'Sample']))
                ->whereNull('deleted_at')->delete();
            DB::table('dataset_spatial_demos')->where('label', 'like', '[DEMO-PETA]%')->delete();
        });

        $this->command?->info('Data resmi tahun 2024 dimuat untuk indikator dari 15 kecamatan dan 5 OPD; record berlabel sampel dinonaktifkan.');
    }

    private function ensureDistrictFields($datasets): void
    {
        $definitions = [
            '3.26' => ['kecamatan' => 'reference', 'tahun' => 'integer', 'kategori' => 'string', 'jumlah' => 'integer'],
            '3.35' => ['kecamatan' => 'reference', 'jk' => 'string', 'usia_kerja' => 'integer', 'jumlah_pengangguran' => 'integer', 'tpt' => 'decimal'],
            '3.36' => ['kecamatan' => 'reference', 'jk' => 'string', 'usia_kerja' => 'integer', 'angkatan_kerja' => 'integer', 'tpak' => 'decimal'],
            '5.59' => ['kategori' => 'string', 'uraian' => 'string', 'nilai' => 'decimal'],
            '6.68' => ['jenis' => 'string', 'jumlah_unit' => 'integer', 'tenaga_kerja' => 'integer'],
            '6.71' => ['kecamatan' => 'reference', 'jumlah_pasar' => 'integer', 'jumlah_bangunan' => 'integer'],
        ];
        foreach ($definitions as $code => $fields) {
            $dataset = $datasets->get($code);
            if (! $dataset) {
                throw new RuntimeException("Dataset {$code} tidak ditemukan.");
            }
            foreach ($fields as $name => $type) {
                $dataset->fields()->firstOrCreate(['nama' => $name], [
                    'label' => ucwords(str_replace('_', ' ', $name)),
                    'tipe' => $type,
                    'lookup_source' => $name === 'kecamatan' ? 'wilayah' : null,
                    'is_dimension' => in_array($type, ['reference', 'string'], true),
                    'urutan' => ((int) $dataset->fields()->max('urutan')) + 1,
                ]);
            }
        }
    }

    private function seedDistrictRows(Dataset $dataset, $districts, array $rows, string $source): void
    {
        $districtIds = $districts->pluck('id')->all();
        foreach ($rows as $values) {
            if (! in_array($values['kecamatan'], $districtIds, true)) {
                throw new RuntimeException('Kecamatan pada data sumber tidak ditemukan.');
            }
            $query = $dataset->values()->withTrashed()->where('tahun', 2024)
                ->where('wilayah_type', Wilayah::TIPE_KECAMATAN)->where('wilayah_id', $values['kecamatan']);
            if (isset($values['kategori'])) {
                $query->where('values->kategori', $values['kategori']);
            }
            $record = $query->first() ?? new DatasetValue(['dataset_id' => $dataset->id, 'tahun' => 2024]);
            $record->fill([
                'periode_label' => '2024', 'wilayah_type' => Wilayah::TIPE_KECAMATAN,
                'wilayah_id' => $values['kecamatan'], 'values' => $values,
                'status' => DatasetValue::STATUS_PUBLISHED, 'status_verifikasi' => DatasetValue::STATUS_PUBLISHED,
                'notes' => $source,
            ]);
            $record->published_at ??= now();
            $record->deleted_at = null;
            $record->save();
        }
    }

    private function seedRows(Dataset $dataset, string $dimensionField, array $rows, string $source): void
    {
        foreach ($rows as $values) {
            $dimensionValue = $values[$dimensionField] ?? null;
            $record = $dataset->values()->withTrashed()->where('tahun', 2024)->get()
                ->first(fn (DatasetValue $value) => data_get($value->values, $dimensionField) === $dimensionValue);
            $record ??= new DatasetValue(['dataset_id' => $dataset->id, 'tahun' => 2024]);
            $record->periode_label = '2024';
            $record->wilayah_type = 'kabupaten';
            $record->wilayah_id = null;
            $record->values = $values;
            $record->status = DatasetValue::STATUS_PUBLISHED;
            $record->status_verifikasi = DatasetValue::STATUS_PUBLISHED;
            $record->published_at ??= now();
            $record->notes = $source;
            $record->deleted_at = null;
            $record->save();
        }
    }

    private function assertDatasetFields($datasets): void
    {
        $requirements = [
            '2.8' => ['golongan', 'tahun', 'jumlah'],
            '2.9' => ['eselon', 'tahun', 'jumlah'],
            '2.10' => ['jabatan', 'tahun', 'jumlah'],
            '2.11' => ['pendidikan', 'tahun', 'jumlah'],
            '3.12' => ['kecamatan', 'kk_laki', 'kk_perempuan'],
            '3.13' => ['kecamatan', 'penduduk_laki', 'penduduk_perempuan'],
            '5.60' => ['jenis', 'jumlah'],
            '6.64' => ['jenis_usaha', 'jumlah_usaha', 'nilai_investasi'],
            '6.65' => ['jenis_izin', 'jumlah_pemohon'],
            '6.66' => ['tahun', 'indikator', 'jumlah'],
        ];

        foreach ($requirements as $code => $requiredFields) {
            $dataset = $datasets->get($code);
            if (! $dataset) throw new RuntimeException("Dataset resmi {$code} tidak ditemukan; tidak ada data yang diubah.");
            $actual = $dataset->fields()->pluck('nama')->all();
            $missing = array_diff($requiredFields, $actual);
            if ($missing !== []) throw new RuntimeException("Dataset {$code} tidak memiliki field: " . implode(', ', $missing));
        }

        // Match the published education categories used in table 2.11.
        $datasets->get('2.11')->fields()->where('nama', 'pendidikan')->update([
            'options' => json_encode(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1/D4', 'S2'], JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function assertSourceTotals(array $rows, array $expected, string $label): void
    {
        $male = array_sum(array_column($rows, 0));
        $female = array_sum(array_column($rows, 1));
        $reportedTotal = array_sum(array_column($rows, 2));
        if ([$male, $female, $reportedTotal] !== $expected) {
            throw new RuntimeException("Jumlah baris {$label} tidak sama dengan total pada tabel sumber; tidak ada data yang diubah.");
        }
    }
}
