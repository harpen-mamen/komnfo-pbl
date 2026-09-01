<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\FasilitasKesehatan;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class FasilitasKesehatanSeeder extends Seeder
{
    public function run(): void
    {
        $kecamatanList = Kecamatan::with('desas')->get()->keyBy('nama');

        $puskesData = [
            ['kecamatan' => 'Tahuna', 'desa' => 'Lakitana', 'nama' => 'Puskesmas Tahuna', 'jenis' => 'Puskesmas', 'kode' => 'PKM-TAH-001', 'alamat' => 'Jl. Sudirman No. 1, Tahuna', 'kepala' => 'dr. Jonnet Waroka', 'nip' => '197601152000031001', 'tahun' => 1975, 'posyandu' => 12, 'pustu' => 4, 'kader' => 48, 'lat' => 3.5944, 'lng' => 125.5026],
            ['kecamatan' => 'Tahuna', 'desa' => 'Lakitana', 'nama' => 'Puskesmas Pembantu Tahuna Timur', 'jenis' => 'Pusat Pembantu', 'kode' => 'PST-TAH-001', 'alamat' => 'Jl. Sam Ratulangi, Tahuna Timur', 'kepala' => '-', 'nip' => '', 'tahun' => 1985, 'posyandu' => 6, 'pustu' => 1, 'kader' => 24, 'lat' => 3.5900, 'lng' => 125.5100],
            ['kecamatan' => 'Tabukan Selatan', 'desa' => 'Liang', 'nama' => 'Puskesmas Tabukan Selatan', 'jenis' => 'Puskesmas', 'kode' => 'PKM-TBS-001', 'alamat' => 'Jl. Trans Sulawesi, Liang', 'kepala' => 'dr. Semion Sumual', 'nip' => '198005202002011001', 'tahun' => 1980, 'posyandu' => 8, 'pustu' => 3, 'kader' => 32, 'lat' => 3.3200, 'lng' => 125.4800],
            ['kecamatan' => 'Tabukan Selatan', 'desa' => 'Karatung', 'nama' => 'Pusat Pembantu Karatung', 'jenis' => 'Pusat Pembantu', 'kode' => 'PST-TBS-001', 'alamat' => 'Desa Karatung', 'kepala' => '-', 'nip' => '', 'tahun' => 1990, 'posyandu' => 4, 'pustu' => 1, 'kader' => 16, 'lat' => 3.3000, 'lng' => 125.4900],
            ['kecamatan' => 'Tabukan Tengah', 'desa' => 'Tarohan', 'nama' => 'Puskesmas Tabukan Tengah', 'jenis' => 'Puskesmas', 'kode' => 'PKM-TT-001', 'alamat' => 'Jl. Poros, Tarohan', 'kepala' => 'dr. Vonny Katuuk', 'nip' => '197903102001022001', 'tahun' => 1982, 'posyandu' => 10, 'pustu' => 4, 'kader' => 40, 'lat' => 3.4500, 'lng' => 125.5200],
            ['kecamatan' => 'Tabukan Utara', 'desa' => 'Mangaas', 'nama' => 'Puskesmas Tabukan Utara', 'jenis' => 'Puskesmas', 'kode' => 'PKM-TUT-001', 'alamat' => 'Jl. Utama, Mangaas', 'kepala' => 'dr. Hengky Kaligis', 'nip' => '198203152003011001', 'tahun' => 1988, 'posyandu' => 7, 'pustu' => 2, 'kader' => 28, 'lat' => 3.5500, 'lng' => 125.5300],
            ['kecamatan' => 'Manganitu', 'desa' => 'Manganitu', 'nama' => 'Puskesmas Manganitu', 'jenis' => 'Puskesmas', 'kode' => 'PKM-MGT-001', 'alamat' => 'Jl. Trans, Manganitu', 'kepala' => 'dr. Meidy Turukmurti', 'nip' => '197705251999032001', 'tahun' => 1978, 'posyandu' => 9, 'pustu' => 3, 'kader' => 36, 'lat' => 3.6800, 'lng' => 125.4500],
            ['kecamatan' => 'Tatoareng', 'desa' => 'Tatoareng', 'nama' => 'Puskesmas Tatoareng', 'jenis' => 'Puskesmas', 'kode' => 'PKM-TAT-001', 'alamat' => 'Desa Tatoareng', 'kepala' => 'dr. Rinto Mandagi', 'nip' => '198109202000011001', 'tahun' => 1995, 'posyandu' => 5, 'pustu' => 2, 'kader' => 20, 'lat' => 3.2500, 'lng' => 125.4200],
            ['kecamatan' => 'Tamako', 'desa' => 'KalongKD', 'nama' => 'Puskesmas Tamako', 'jenis' => 'Puskesmas', 'kode' => 'PKM-TMK-001', 'alamat' => 'Jl. Poros, Kalong KD', 'kepala' => 'dr. Stenly Wauran', 'nip' => '198011301999011001', 'tahun' => 1983, 'posyandu' => 8, 'pustu' => 3, 'kader' => 32, 'lat' => 3.2100, 'lng' => 125.5500],
            ['kecamatan' => 'Kecamatan Selatan', 'desa' => 'Baru', 'nama' => 'Puskesmas Mang Schae', 'jenis' => 'Puskesmas', 'kode' => 'PKM-MSH-001', 'alamat' => 'Jl. Utama, Baru', 'kepala' => 'dr. Johan Kapugu', 'nip' => '197812102002011001', 'tahun' => 1990, 'posyandu' => 6, 'pustu' => 2, 'kader' => 24, 'lat' => 3.7200, 'lng' => 125.4800],
            ['kecamatan' => 'Kendahe', 'desa' => 'Kendahe', 'nama' => 'Puskesmas Kendahe', 'jenis' => 'Puskesmas', 'kode' => 'PKM-KDH-001', 'alamat' => 'Desa Kendahe', 'kepala' => 'dr. Dolvin Lintong', 'nip' => '198404202002011001', 'tahun' => 1992, 'posyandu' => 5, 'pustu' => 2, 'kader' => 20, 'lat' => 3.7500, 'lng' => 125.5100],
        ];

        foreach ($puskesData as $data) {
            $kec = $kecamatanList->get($data['kecamatan']);
            if (!$kec) { $this->command->warn("Kecamatan {$data['kecamatan']} not found"); continue; }
            $desa = $kec->desas->firstWhere('nama', $data['desa']);
            FasilitasKesehatan::firstOrCreate(
                ['kode_faskes' => $data['kode']],
                [
                    'kecamatan_id' => $kec->id, 'desa_id' => $desa?->id,
                    'nama' => $data['nama'],
                    'jenis' => $data['jenis'], 'alamat' => $data['alamat'],
                    'telepon' => '-', 'kepala_puskes' => $data['kepala'],
                    'nip_kepala' => $data['nip'] ?: null, 'tahun_berdiri' => $data['tahun'],
                    'status_operasional' => true, 'koordinat_lat' => $data['lat'],
                    'koordinat_lng' => $data['lng'], 'jumlah_posyandu' => $data['posyandu'],
                    'jumlah_pustu' => $data['pustu'], 'jumlah_kader' => $data['kader'],
                    'created_by' => null,
                ]
            );
        }
        $created = FasilitasKesehatan::count();
        $this->command->info("FasilitasKesehatanSeeder: {$created} total puskes records.");
    }
}
