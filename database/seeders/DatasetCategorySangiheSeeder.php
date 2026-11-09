<?php

namespace Database\Seeders;

use App\Models\DatasetCategory;
use Illuminate\Database\Seeder;

/**
 * SEEDER: 32 Urusan Pemerintah (Perpres 39/2019 + UU 23/2014)
 *
 * Berdasarkan:
 * - UU No. 23 Tahun 2014 tentang Pemerintahan Daerah
 * - Perpres 39/2019 tentang Satu Data Indonesia
 * - Lampiran UU 23/2014 yang membagi 32 urusan pemerintah
 */
class DatasetCategorySangiheSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            // 6 Urusan Pelayanan Dasar (Wajib Pelayanan Dasar)
            ["kode" => "1.01", "nama" => "Pendidikan", "kelompok" => "pelayanan_dasar", "urutan" => 1],
            ["kode" => "1.02", "nama" => "Kesehatan", "kelompok" => "pelayanan_dasar", "urutan" => 2],
            ["kode" => "1.03", "nama" => "Pekerjaan Umum dan Penataan Ruang", "kelompok" => "pelayanan_dasar", "urutan" => 3],
            ["kode" => "1.04", "nama" => "Perumahan Rakyat dan Kawasan Permukiman", "kelompok" => "pelayanan_dasar", "urutan" => 4],
            ["kode" => "1.05", "nama" => "Ketentraman dan Ketertiban Umum serta Perlindungan Masyarakat", "kelompok" => "pelayanan_dasar", "urutan" => 5],
            ["kode" => "1.06", "nama" => "Sosial", "kelompok" => "pelayanan_dasar", "urutan" => 6],

            // 18 Urusan Wajib Non-Pelayanan Dasar
            ["kode" => "2.01", "nama" => "Tenaga Kerja", "kelompok" => "non_pelayanan_dasar", "urutan" => 7],
            ["kode" => "2.02", "nama" => "Pemberdayaan Perempuan dan Perlindungan Anak", "kelompok" => "non_pelayanan_dasar", "urutan" => 8],
            ["kode" => "2.03", "nama" => "Pangan", "kelompok" => "non_pelayanan_dasar", "urutan" => 9],
            ["kode" => "2.04", "nama" => "Pertanahan", "kelompok" => "non_pelayanan_dasar", "urutan" => 10],
            ["kode" => "2.05", "nama" => "Lingkungan Hidup", "kelompok" => "non_pelayanan_dasar", "urutan" => 11],
            ["kode" => "2.06", "nama" => "Administrasi Kependudukan dan Pencatatan Sipil", "kelompok" => "non_pelayanan_dasar", "urutan" => 12],
            ["kode" => "2.07", "nama" => "Pemberdayaan Masyarakat dan Desa", "kelompok" => "non_pelayanan_dasar", "urutan" => 13],
            ["kode" => "2.08", "nama" => "Pengendalian Penduduk dan Keluarga Berencana", "kelompok" => "non_pelayanan_dasar", "urutan" => 14],
            ["kode" => "2.09", "nama" => "Perhubungan", "kelompok" => "non_pelayanan_dasar", "urutan" => 15],
            ["kode" => "2.10", "nama" => "Komunikasi dan Informatika", "kelompok" => "non_pelayanan_dasar", "urutan" => 16],
            ["kode" => "2.11", "nama" => "Koperasi, Usaha Kecil dan Menengah", "kelompok" => "non_pelayanan_dasar", "urutan" => 17],
            ["kode" => "2.12", "nama" => "Penanaman Modal", "kelompok" => "non_pelayanan_dasar", "urutan" => 18],
            ["kode" => "2.13", "nama" => "Kepemudaan dan Olahraga", "kelompok" => "non_pelayanan_dasar", "urutan" => 19],
            ["kode" => "2.14", "nama" => "Statistik", "kelompok" => "non_pelayanan_dasar", "urutan" => 20],
            ["kode" => "2.15", "nama" => "Persandian", "kelompok" => "non_pelayanan_dasar", "urutan" => 21],
            ["kode" => "2.16", "nama" => "Kebudayaan", "kelompok" => "non_pelayanan_dasar", "urutan" => 22],
            ["kode" => "2.17", "nama" => "Perpustakaan", "kelompok" => "non_pelayanan_dasar", "urutan" => 23],
            ["kode" => "2.18", "nama" => "Kearsipan", "kelompok" => "non_pelayanan_dasar", "urutan" => 24],

            // 8 Urusan Pilihan
            ["kode" => "3.01", "nama" => "Kelautan dan Perikanan", "kelompok" => "urusan_pilihan", "urutan" => 25],
            ["kode" => "3.02", "nama" => "Pariwisata", "kelompok" => "urusan_pilihan", "urutan" => 26],
            ["kode" => "3.03", "nama" => "Pertanian", "kelompok" => "urusan_pilihan", "urutan" => 27],
            ["kode" => "3.04", "nama" => "Kehutanan", "kelompok" => "urusan_pilihan", "urutan" => 28],
            ["kode" => "3.05", "nama" => "Energi dan Sumber Daya Mineral", "kelompok" => "urusan_pilihan", "urutan" => 29],
            ["kode" => "3.06", "nama" => "Perdagangan", "kelompok" => "urusan_pilihan", "urutan" => 30],
            ["kode" => "3.07", "nama" => "Perindustrian", "kelompok" => "urusan_pilihan", "urutan" => 31],
            ["kode" => "3.08", "nama" => "Transmigrasi", "kelompok" => "urusan_pilihan", "urutan" => 32],
        ];

        foreach ($kategori as $k) {
            DatasetCategory::updateOrCreate(
                ["kode" => $k["kode"]],
                [
                    "nama" => $k["nama"],
                    "kelompok" => $k["kelompok"],
                    "urutan" => $k["urutan"],
                    "aktif" => true,
                ]
            );
        }

        $this->command->info("Berhasil seed " . count($kategori) . " urusan pemerintah");
    }
}