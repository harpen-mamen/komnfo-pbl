<?php

namespace Database\Seeders;

use App\Models\KodeReferensi;
use Illuminate\Database\Seeder;

/**
 * SDI: Seed Kode Referensi bersama (Perpres 39/2019)
 *
 * Berisi subset kode referensi standar yang dipakai dataset Sangihe:
 *   - Kodefikasi Wilayah BPS (Provinsi Sulawesi Utara + Kab Sangihe)
 *   - KBLI (5 kategori utama)
 *   - ICD-10 (10 penyakit prioritas)
 *   - Kode Pendidikan (jenjang)
 *   - Status Pekerjaan, Agama, dll
 */
class KodeReferensiSdkiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. KODEFIKASI WILAYAH BPS
        $this->seedWilayahBps();

        // 2. KBLI (Klasifikasi Baku Lapangan Usaha Indonesia)
        $this->seedKbli();

        // 3. ICD-10 (Penyakit)
        $this->seedIcd10();

        // 4. KODE PENDIDIKAN
        $this->seedKodePendidikan();

        // 5. AGAMA
        $this->seedAgama();

        // 6. JENIS PEKERJAAN
        $this->seedPekerjaan();
    }

    private function seedWilayahBps(): void
    {
        $data = [
            ['kode' => '71', 'nama' => 'SULAWESI UTARA', 'level' => 1],
            ['kode' => '71.03', 'nama' => 'KAB. KEPULAUAN SANGIHE', 'level' => 2],
            // Kecamatan (contoh subset, lengkapnya 15)
            ['kode' => '71.03.01', 'nama' => 'Kec. Tahuna', 'parent' => '71.03'],
            ['kode' => '71.03.02', 'nama' => 'Kec. Tahuna Barat', 'parent' => '71.03'],
            ['kode' => '71.03.03', 'nama' => 'Kec. Tahuna Timur', 'parent' => '71.03'],
            ['kode' => '71.03.04', 'nama' => 'Kec. Tabukan Utara', 'parent' => '71.03'],
            ['kode' => '71.03.05', 'nama' => 'Kec. Tabukan Tengah', 'parent' => '71.03'],
            ['kode' => '71.03.06', 'nama' => 'Kec. Tabukan Selatan', 'parent' => '71.03'],
            ['kode' => '71.03.07', 'nama' => 'Kec. Kendahe', 'parent' => '71.03'],
            ['kode' => '71.03.08', 'nama' => 'Kec. Manganitu', 'parent' => '71.03'],
            ['kode' => '71.03.09', 'nama' => 'Kec. Manganitu Selatan', 'parent' => '71.03'],
            ['kode' => '71.03.10', 'nama' => 'Kec. Tamako', 'parent' => '71.03'],
            ['kode' => '71.03.11', 'nama' => 'Kec. Tatoareng', 'parent' => '71.03'],
            ['kode' => '71.03.12', 'nama' => 'Kec. Nusa Tabukan', 'parent' => '71.03'],
            ['kode' => '71.03.13', 'nama' => 'Kec. Marore', 'parent' => '71.03'],
            ['kode' => '71.03.14', 'nama' => 'Kec. Kepulauan Marore', 'parent' => '71.03'],
        ];
        foreach ($data as $row) {
            KodeReferensi::updateOrCreate(
                ['jenis' => 'kode_wilayah', 'kode' => $row['kode']],
                [
                    'nama' => $row['nama'],
                    'level' => $row['level'] ?? 0,
                    'parent_id' => isset($row['parent'])
                        ? KodeReferensi::where('jenis', 'kode_wilayah')->where('kode', $row['parent'])->value('id')
                        : null,
                    'standar_sumber' => 'BPS',
                    'aktif' => true,
                ]
            );
        }
    }

    private function seedKbli(): void
    {
        $data = [
            ['kode' => 'A', 'nama' => 'Pertanian, Kehutanan, Perikanan'],
            ['kode' => 'B', 'nama' => 'Pertambangan dan Penggalian'],
            ['kode' => 'C', 'nama' => 'Industri Pengolahan'],
            ['kode' => 'D', 'nama' => 'Pengadaan Listrik, Gas'],
            ['kode' => 'E', 'nama' => 'Pengadaan Air, Sampah'],
            ['kode' => 'F', 'nama' => 'Konstruksi'],
            ['kode' => 'G', 'nama' => 'Perdagangan Besar/Eceran'],
            ['kode' => 'H', 'nama' => 'Transportasi, Pergudangan'],
            ['kode' => 'I', 'nama' => 'Penyediaan Akomodasi, Makan Minum'],
            ['kode' => 'J', 'nama' => 'Informasi dan Komunikasi'],
            ['kode' => 'K', 'nama' => 'Jasa Keuangan dan Asuransi'],
            ['kode' => 'L', 'nama' => 'Real Estat'],
            ['kode' => 'M', 'nama' => 'Jasa Profesional, Ilmiah, Teknis'],
            ['kode' => 'N', 'nama' => 'Jasa Persewaan'],
            ['kode' => 'O', 'nama' => 'Administrasi Pemerintahan'],
            ['kode' => 'P', 'nama' => 'Jasa Pendidikan'],
            ['kode' => 'Q', 'nama' => 'Jasa Kesehatan'],
            ['kode' => 'R', 'nama' => 'Kesenian, Hiburan, Rekreasi'],
            ['kode' => 'S', 'nama' => 'Jasa Lainnya'],
        ];
        foreach ($data as $row) {
            KodeReferensi::updateOrCreate(
                ['jenis' => 'kbli', 'kode' => $row['kode']],
                ['nama' => $row['nama'], 'standar_sumber' => 'BPS', 'aktif' => true]
            );
        }
    }

    private function seedIcd10(): void
    {
        $data = [
            ['kode' => 'A09', 'nama' => 'Diare dan gastroenteritis'],
            ['kode' => 'A15', 'nama' => 'Tuberkulosis paru'],
            ['kode' => 'A90', 'nama' => 'Demam berdarah dengue'],
['kode' => 'B50', 'nama' => 'Malaria falciparum'],
            ['kode' => 'B54', 'nama' => 'Malaria tanpa spesifikasi'],
            ['kode' => 'E11', 'nama' => 'Diabetes mellitus tipe 2'],
            ['kode' => 'I10', 'nama' => 'Hipertensi esensial'],
            ['kode' => 'J00', 'nama' => 'Infeksi saluran pernapasan atas akut'],
            ['kode' => 'J18', 'nama' => 'Pneumonia'],
            ['kode' => 'O80', 'nama' => 'Persalinan tunggal spontan'],
            ['kode' => 'O82', 'nama' => 'Persalinan dengan operasi caesar'],
        ];
        foreach ($data as $row) {
            KodeReferensi::updateOrCreate(
                ['jenis' => 'icd10', 'kode' => $row['kode']],
                ['nama' => $row['nama'], 'standar_sumber' => 'Kemenkes', 'aktif' => true]
            );
        }
    }

    private function seedKodePendidikan(): void
    {
        $data = [
            ['kode' => 'SD', 'nama' => 'Sekolah Dasar'],
            ['kode' => 'SMP', 'nama' => 'Sekolah Menengah Pertama'],
            ['kode' => 'SMA', 'nama' => 'Sekolah Menengah Atas'],
            ['kode' => 'SMK', 'nama' => 'Sekolah Menengah Kejuruan'],
            ['kode' => 'SLB', 'nama' => 'Sekolah Luar Biasa'],
            ['kode' => 'PAUD', 'nama' => 'Pendidikan Anak Usia Dini'],
            ['kode' => 'PKBM', 'nama' => 'Pusat Kegiatan Belajar Masyarakat'],
        ];
        foreach ($data as $row) {
            KodeReferensi::updateOrCreate(
                ['jenis' => 'kode_pendidikan', 'kode' => $row['kode']],
                ['nama' => $row['nama'], 'standar_sumber' => 'Kemendikbud', 'aktif' => true]
            );
        }
    }

    private function seedAgama(): void
    {
        $data = [
            ['kode' => '01', 'nama' => 'Islam'],
            ['kode' => '02', 'nama' => 'Kristen Protestan'],
            ['kode' => '03', 'nama' => 'Katolik'],
            ['kode' => '04', 'nama' => 'Hindu'],
            ['kode' => '05', 'nama' => 'Buddha'],
            ['kode' => '06', 'nama' => 'Konghucu'],
            ['kode' => '07', 'nama' => 'Kepercayaan'],
        ];
        foreach ($data as $row) {
            KodeReferensi::updateOrCreate(
                ['jenis' => 'agama', 'kode' => $row['kode']],
                ['nama' => $row['nama'], 'standar_sumber' => 'BPS', 'aktif' => true]
            );
        }
    }

    private function seedPekerjaan(): void
    {
        $data = [
            ['kode' => '01', 'nama' => 'Petani'],
            ['kode' => '02', 'nama' => 'Nelayan'],
            ['kode' => '03', 'nama' => 'Pedagang'],
            ['kode' => '04', 'nama' => 'PNS'],
            ['kode' => '05', 'nama' => 'TNI/Polri'],
            ['kode' => '06', 'nama' => 'Karyawan Swasta'],
            ['kode' => '07', 'nama' => 'Wiraswasta'],
            ['kode' => '08', 'nama' => 'Buruh'],
            ['kode' => '09', 'nama' => 'Pensiunan'],
            ['kode' => '10', 'nama' => 'Ibu Rumah Tangga'],
            ['kode' => '11', 'nama' => 'Pelajar/Mahasiswa'],
            ['kode' => '12', 'nama' => 'Tidak Bekerja'],
        ];
        foreach ($data as $row) {
            KodeReferensi::updateOrCreate(
                ['jenis' => 'pekerjaan', 'kode' => $row['kode']],
                ['nama' => $row['nama'], 'standar_sumber' => 'BPS', 'aktif' => true]
            );
        }
    }
}
