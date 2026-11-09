<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER: Wilayah Administratif Kabupaten Kepulauan Sangihe
 *
 * Sesuai:
 * - UU 29/1959 tentang Pembentukan Daerah Tingkat II di Sulawesi
 * - PP 59/2014 tentang perubahan nama Kab. Sangihe dan Talaud -> Kab. Kep. Sangihe
 *
 * Struktur:
 * 1 Kabupaten -> 15 Kecamatan. Desa/kampung diisi oleh SangiheDesaSeeder.
 * 105 Pulau, 26 berpenduduk, 79 tidak berpenduduk
 */
class WilayahSangiheSeeder extends Seeder
{
    public function run(): void
    {
        $kecamatans = [
            ["nama" => "Tabukan Utara", "kode_bps" => "710308", "kepala" => ""],
            ["nama" => "Nusa Tabukan", "kode_bps" => "710309", "kepala" => ""],
            ["nama" => "Manganitu Selatan", "kode_bps" => "710310", "kepala" => ""],
            ["nama" => "Tatoareng", "kode_bps" => "710311", "kepala" => ""],
            ["nama" => "Tamako", "kode_bps" => "710312", "kepala" => ""],
            ["nama" => "Manganitu", "kode_bps" => "710313", "kepala" => ""],
            ["nama" => "Tabukan Tengah", "kode_bps" => "710314", "kepala" => ""],
            ["nama" => "Tabukan Selatan", "kode_bps" => "710315", "kepala" => ""],
            ["nama" => "Kendahe", "kode_bps" => "710316", "kepala" => ""],
            ["nama" => "Tahuna", "kode_bps" => "710317", "kepala" => ""],
            ["nama" => "Tabukan Selatan Tengah", "kode_bps" => "710319", "kepala" => ""],
            ["nama" => "Tabukan Selatan Tenggara", "kode_bps" => "710320", "kepala" => ""],
            ["nama" => "Tahuna Barat", "kode_bps" => "710323", "kepala" => ""],
            ["nama" => "Tahuna Timur", "kode_bps" => "710324", "kepala" => ""],
            ["nama" => "Kepulauan Marore", "kode_bps" => "710325", "kepala" => ""],
        ];

        // Upsert the administrative root so rerunning the seeder is safe.
        $now = now();
        DB::table("wilayahs")->updateOrInsert([
            "tipe" => "kabupaten",
            "nama" => "Kabupaten Kepulauan Sangihe",
        ], [
            "parent_id" => null,
            "tipe" => "kabupaten",
            "kode_bps" => "7103",
            "nama" => "Kabupaten Kepulauan Sangihe",
            "singkatan" => "Sangihe",
            "latitude" => "3.5",
            "longitude" => "125.5",
            "metadata" => json_encode([
                "luas_total_km2" => 11863.58,
                "luas_darat_km2" => 736.98,
                "luas_laut_km2" => 11126.61,
                "jumlah_pulau" => 105,
                "pulau_berpenduduk" => 26,
                "ibu_kota" => "Tahuna",
                "provinsi" => "Sulawesi Utara",
                "batas_utara" => "Republik Filipina dan Kab. Kep. Talaud",
                "batas_selatan" => "Kab. Kep. Siau Tagulandang Biaro",
                "batas_timur" => "Kab. Kep. Talaud, Samudra Pasifik, Laut Maluku",
                "batas_barat" => "Laut Sulawesi",
            ]),
            "aktif" => true,
            "updated_at" => $now,
        ]);
        $kabId = DB::table("wilayahs")
            ->where("tipe", "kabupaten")
            ->where("nama", "Kabupaten Kepulauan Sangihe")
            ->value("id");

        // Step 2: Insert 15 Kecamatan
        foreach ($kecamatans as $k) {
            DB::table("wilayahs")->updateOrInsert([
                "parent_id" => $kabId,
                "tipe" => "kecamatan",
                "nama" => $k["nama"],
            ], [
                "kode_bps" => $k["kode_bps"],
                "kepala_wilayah" => $k["kepala"],
                "aktif" => true,
                "updated_at" => $now,
            ]);
        }

        $count = DB::table("wilayahs")->where("aktif", true)->count();
        $this->command->info("Master kabupaten dan 15 kecamatan siap; total wilayah aktif: " . $count);
    }
}
