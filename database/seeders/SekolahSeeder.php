<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kecList = \App\Models\Kecamatan::all()->keyBy('nama');

        $sekolahData = [
            ['nama' => 'SD Negeri 1 Tahuna', 'npsn' => '40100101', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Liem Djunaedi, S.Pd.', 'nip' => '196501151985011001', 'tahun' => 1945, 'akr' => 'A'],
            ['nama' => 'SD Negeri 2 Tahuna', 'npsn' => '40100102', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Rudy Wuisan, S.Pd.', 'nip' => '196708121987011001', 'tahun' => 1950, 'akr' => 'A'],
            ['nama' => 'SD Negeri 3 Tahuna', 'npsn' => '40100103', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Joice Katuuk, S.Pd.', 'nip' => '197002201990012001', 'tahun' => 1955, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Tahuna', 'npsn' => '40100111', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Drs. Max Turukmurti', 'nip' => '196103151984011001', 'tahun' => 1960, 'akr' => 'A'],
            ['nama' => 'SMP Negeri 2 Tahuna', 'npsn' => '40100112', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Semion Sumual, S.Pd.', 'nip' => '196505201986011001', 'tahun' => 1980, 'akr' => 'B'],
            ['nama' => 'SMA Negeri 1 Tahuna', 'npsn' => '40100121', 'jenjang' => 'SMA', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Drs. Hengky Waworuntu', 'nip' => '196205101982011001', 'tahun' => 1965, 'akr' => 'A'],
            ['nama' => 'SMK Negeri 1 Tahuna', 'npsn' => '40100131', 'jenjang' => 'SMK', 'status' => 'Negeri', 'kec' => 'Tahuna', 'kepala' => 'Ir. Vonny Luntungan', 'nip' => '196807151990012001', 'tahun' => 1990, 'akr' => 'A'],
            ['nama' => 'SD Negeri 1 Tabukan Selatan', 'npsn' => '40100201', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tabukan Selatan', 'kepala' => 'Julius Damus, S.Pd.', 'nip' => '196809121988011001', 'tahun' => 1950, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Tabukan Selatan', 'npsn' => '40100211', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tabukan Selatan', 'kepala' => 'Drs. Stenly Mongil', 'nip' => '196505011986011001', 'tahun' => 1975, 'akr' => 'B'],
            ['nama' => 'SD Negeri 1 Tabukan Tengah', 'npsn' => '40100301', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tabukan Tengah', 'kepala' => 'Meidy Kapugu, S.Pd.', 'nip' => '197010101991011001', 'tahun' => 1955, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Tabukan Tengah', 'npsn' => '40100311', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tabukan Tengah', 'kepala' => 'Drs. Dolvin Wauran', 'nip' => '196603101985011001', 'tahun' => 1980, 'akr' => 'B'],
            ['nama' => 'SMA Negeri 1 Tabukan Tengah', 'npsn' => '40100321', 'jenjang' => 'SMA', 'status' => 'Negeri', 'kec' => 'Tabukan Tengah', 'kepala' => 'Drs. Johan Mandagi', 'nip' => '196404101983011001', 'tahun' => 1985, 'akr' => 'B'],
            ['nama' => 'SD Negeri 1 Manganitu', 'npsn' => '40100401', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Manganitu', 'kepala' => 'Rinto Turukmurti, S.Pd.', 'nip' => '197105151992011001', 'tahun' => 1955, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Manganitu', 'npsn' => '40100411', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Manganitu', 'kepala' => 'Drs. Semion Katuuk', 'nip' => '196508121987011001', 'tahun' => 1975, 'akr' => 'B'],
            ['nama' => 'SMA Negeri 1 Manganitu', 'npsn' => '40100421', 'jenjang' => 'SMA', 'status' => 'Negeri', 'kec' => 'Manganitu', 'kepala' => 'Drs. Max Mongil', 'nip' => '196209151980011001', 'tahun' => 1980, 'akr' => 'B'],
            ['nama' => 'SD Negeri 1 Tamako', 'npsn' => '40100501', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tamako', 'kepala' => 'Liem Turukmurti, S.Pd.', 'nip' => '197210101993011001', 'tahun' => 1955, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Tamako', 'npsn' => '40100511', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tamako', 'kepala' => 'Drs. Hengky Kapugu', 'nip' => '196609151988011001', 'tahun' => 1980, 'akr' => 'B'],
            ['nama' => 'SD Negeri 1 Tatoareng', 'npsn' => '40100601', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tatoareng', 'kepala' => 'Joice Mandagi, S.Pd.', 'nip' => '197308151994011001', 'tahun' => 1965, 'akr' => 'C'],
            ['nama' => 'SMP Negeri 1 Tatoareng', 'npsn' => '40100611', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tatoareng', 'kepala' => 'Drs. Stenly Katuuk', 'nip' => '196707151989011001', 'tahun' => 1985, 'akr' => 'C'],
            ['nama' => 'SD Negeri 1 Tabukan Utara', 'npsn' => '40100701', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Tabukan Utara', 'kepala' => 'Julius Wauran, S.Pd.', 'nip' => '197406151995011001', 'tahun' => 1960, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Tabukan Utara', 'npsn' => '40100711', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Tabukan Utara', 'kepala' => 'Drs. Meidy Damus', 'nip' => '196810121990011001', 'tahun' => 1982, 'akr' => 'B'],
            ['nama' => 'SD Negeri 1 Kendahe', 'npsn' => '40100801', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Kendahe', 'kepala' => 'Rudy Mongil, S.Pd.', 'nip' => '197510101996011001', 'tahun' => 1965, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Kendahe', 'npsn' => '40100811', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Kendahe', 'kepala' => 'Drs. Dolvin Turukmurti', 'nip' => '196911121991011001', 'tahun' => 1985, 'akr' => 'C'],
            ['nama' => 'SD Negeri 1 Mang Schae', 'npsn' => '40100901', 'jenjang' => 'SD', 'status' => 'Negeri', 'kec' => 'Kecamatan Selatan', 'kepala' => 'Semion Kapugu, S.Pd.', 'nip' => '197608151997011001', 'tahun' => 1968, 'akr' => 'B'],
            ['nama' => 'SMP Negeri 1 Mang Schae', 'npsn' => '40100911', 'jenjang' => 'SMP', 'status' => 'Negeri', 'kec' => 'Kecamatan Selatan', 'kepala' => 'Drs. Johan Wuisan', 'nip' => '197002151992011001', 'tahun' => 1988, 'akr' => 'C'],
        ];

        foreach ($sekolahData as $data) {
            $kec = $kecList->get($data['kec']);
            if (!$kec) { $this->command->warn("Kecamatan {$data['kec']} not found"); continue; }
            \App\Models\Sekolah::firstOrCreate(
                ['npsn' => $data['npsn']],
                [
                    'kecamatan_id' => $kec->id,
                    'nama_sekolah' => $data['nama'],
                    'jenjang' => $data['jenjang'], 'status' => $data['status'],
                    'kepala_sekolah' => $data['kepala'], 'nip_kepala' => $data['nip'],
                    'tahun_berdiri' => $data['tahun'], 'akreditasi' => $data['akr'],
                    'alamat' => $data['nama'] . ', Kec. ' . $data['kec'],
                    'created_by' => null,
                ]
            );
        }
        $created = \App\Models\Sekolah::count();
        $this->command->info("SekolahSeeder: {$created} total sekolah records.");
    }
}
