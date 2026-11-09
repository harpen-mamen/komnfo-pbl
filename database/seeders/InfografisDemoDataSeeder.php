<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Seed clearly marked, repeatable demo records for all active OPD datasets. */
class InfografisDemoDataSeeder extends Seeder
{
    private const MARKER = '[DEMO-INFOGRAFIS]';

    public function run(): void
    {
        $this->command?->warn('Seeder infografis sintetis dinonaktifkan; gunakan data resmi dari OPD.');
    }

    private function categoriesFor(Dataset $dataset, ?DatasetField $dimension): array
    {
        if (! $dimension) return ['Kabupaten'];
        $name = strtolower($dimension->nama . ' ' . $dimension->label);
        $datasetName = strtolower($dataset->nama);

        if (str_contains($datasetName, 'harga pangan') || str_contains($name, 'komoditas') || str_contains($name, 'bahan_pokok')) {
            return ['Beras medium', 'Gula pasir', 'Minyak goreng', 'Telur ayam'];
        }
        $options = $dimension->options;
        if (is_array($options) && $options !== []) {
            $labels = array_is_list($options) ? $options : array_values($options);
            return array_slice(array_map(fn ($value) => is_scalar($value) ? (string) $value : 'Data tidak diketahui', $labels), 0, 8);
        }

        if (str_contains($name, 'jenis_kelamin') || str_contains($name, 'gender')) return ['Laki-laki', 'Perempuan'];
        if (str_contains($name, 'jenjang')) return ['PAUD', 'SD', 'SMP', 'SMA/SMK'];
        if (str_contains($name, 'umur') || str_contains($name, 'usia') || str_contains($name, 'kelompok_umur')) return ['0–4 tahun', '5–14 tahun', '15–24 tahun', '25 tahun ke atas'];
        if (str_contains($name, 'kecamatan')) {
            $names = \App\Models\Wilayah::query()->where('tipe', 'kecamatan')->orderBy('nama')->pluck('nama')->all();
            if ($names !== []) return array_slice($names, 0, 8);
            return ['Tahuna', 'Tabukan Utara', 'Manganitu', 'Tamako'];
        }
        if (str_contains($name, 'desa') || str_contains($name, 'kampung')) {
            $names = \App\Models\Wilayah::query()->where('tipe', 'desa')->orderBy('nama')->pluck('nama')->all();
            if ($names !== []) return array_slice($names, 0, 8);
            return ['Kampung Manente', 'Kampung Bungalawang', 'Kampung Tona', 'Kampung Santiago'];
        }
        if (str_contains($name, 'status')) return ['Aktif', 'Tidak aktif', 'Dalam proses'];
        if (str_contains($name, 'indikator')) {
            if (str_contains($datasetName, 'aki akb')) return ['Angka Kematian Ibu', 'Angka Kematian Bayi', 'Angka Harapan Hidup'];
            if (str_contains($datasetName, 'gender')) return ['Keterwakilan perempuan', 'Partisipasi ekonomi', 'Pendidikan'];
            if (str_contains($datasetName, 'gizi')) return ['Energi', 'Protein', 'Kecukupan konsumsi'];
            return ['Capaian layanan', 'Jumlah penerima', 'Tingkat pemenuhan', 'Cakupan program'];
        }
        if (str_contains($name, 'kelompok_pangan') || str_contains($datasetName, 'pola pangan')) return ['Padi-padian', 'Umbi-umbian', 'Pangan hewani', 'Sayur dan buah'];
        if (str_contains($name, 'jenis') || str_contains($name, 'kategori') || str_contains($name, 'komponen')) {
            if (str_contains($datasetName, 'ikan')) return ['Tuna', 'Cakalang', 'Tongkol', 'Layang'];
            if (str_contains($datasetName, 'pajak')) return ['Pajak bumi dan bangunan', 'Pajak restoran', 'Pajak hotel', 'Pajak reklame'];
            if (str_contains($datasetName, 'jalan')) return ['Baik', 'Sedang', 'Rusak ringan', 'Rusak berat'];
            if (str_contains($datasetName, 'izin')) return ['Izin usaha', 'Izin bangunan', 'Izin lingkungan', 'Izin lainnya'];
            return ['Pelayanan dasar', 'Pembangunan', 'Pemberdayaan', 'Pembinaan'];
        }
        if (str_contains($name, 'program') || str_contains($name, 'kegiatan')) return ['Pelayanan masyarakat', 'Pembinaan dan pengawasan', 'Pembangunan daerah', 'Administrasi pemerintahan'];
        if (str_contains($name, 'media')) return ['Website resmi', 'Media sosial', 'Layanan informasi langsung', 'Media publikasi'];
        if (str_contains($name, 'nama') || str_contains($name, 'lokasi') || str_contains($name, 'pasar') || str_contains($name, 'destinasi')) {
            return $this->namedPlacesFor($datasetName);
        }

        return ['Kabupaten Kepulauan Sangihe', $dataset->opd?->nama ?? 'Perangkat daerah', 'Pelayanan masyarakat', 'Program pembangunan'];
    }

    private function isUsefulDimension(DatasetField $field): bool
    {
        $name = strtolower($field->nama . ' ' . $field->label);
        if (preg_match('/(^|[ _])(nik|npwpd|id|kode|no|nomor|tahun|bulan|periode)([ _]|$)/', $name)) return false;
        if (preg_match('/(^|[ _])(target|alamat|keterangan|catatan|deskripsi)([ _]|$)/', $name)) return false;

        return in_array($field->tipe, ['enum', 'reference', 'string', 'text'], true)
            && preg_match('/jenis|kategori|status|kelompok|umur|usia|gender|kelamin|jenjang|indikator|komponen|kecamatan|desa|kampung|program|kegiatan|media|nama|lokasi|pasar|destinasi|komoditas|pangan|dapil|tipe|kondisi|wilayah/', $name) === 1;
    }

    private function namedPlacesFor(string $datasetName): array
    {
        if (str_contains($datasetName, 'pasar')) return ['Pasar Tahuna', 'Pasar Tamako', 'Pasar Manganitu', 'Pasar Enemawira'];
        if (str_contains($datasetName, 'wisata') || str_contains($datasetName, 'destinasi')) return ['Pantai Tahuna', 'Gunung Awu', 'Pulau Mahumu', 'Air Terjun Kadadima'];
        if (str_contains($datasetName, 'puskesmas') || str_contains($datasetName, 'kesehatan')) return ['Puskesmas Tahuna', 'Puskesmas Tamako', 'Puskesmas Manganitu', 'Puskesmas Kendahe'];
        return ['Tahuna', 'Tamako', 'Manganitu', 'Tabukan Utara'];
    }

    private function valueFor(DatasetField $field, int $index, int $year, Opd $opd): mixed
    {
        $name = strtolower($field->nama . ' ' . $field->label);
        if (preg_match('/(^|[ _])(tahun|year)([ _]|$)/', $name)) return $year;
        return match ($field->tipe) {
            'integer' => $this->numberFor($name, $index, $year),
            'decimal' => round($this->numberFor($name, $index, $year) / 10, 2),
            'boolean' => $index % 4 !== 3,
            'date' => sprintf('%d-%02d-01', $year, (($index * 3) % 12) + 1),
            'datetime' => sprintf('%d-%02d-01 09:00:00', $year, (($index * 3) % 12) + 1),
            'reference' => $this->referenceValue($field, $index, $opd),
            'json' => ['demo' => true, 'urutan' => $index + 1],
            default => $this->stringValue($field, $index, $opd),
        };
    }

    private function numberFor(string $name, int $index, int $year): int
    {
        if (str_contains($name, 'harga') || str_contains($name, 'price')) {
            return 12000 + ($index * 1750) + (max(0, $year - 2023) * 500);
        }
        $base = 20 + (abs(crc32($name)) % 600);
        $yearOffset = max(0, $year - 2023) * max(1, (int) round($base * 0.04));
        if (str_contains($name, 'persen') || str_contains($name, 'rate') || str_contains($name, 'rasio')) {
            return min(100, 55 + (($base + $index * 7 + $yearOffset) % 43));
        }
        return $base + ($index * max(3, (int) round($base * 0.13))) + $yearOffset;
    }

    private function referenceValue(DatasetField $field, int $index, Opd $opd): mixed
    {
        $name = strtolower($field->nama . ' ' . $field->label);
        if (str_contains($name, 'kecamatan') || str_contains($name, 'wilayah')) return ['Tahuna', 'Tabukan Utara', 'Manganitu', 'Tamako'][$index % 4];
        return $opd->singkatan ?: $opd->nama;
    }

    private function stringValue(DatasetField $field, int $index, Opd $opd): string
    {
        $name = strtolower($field->nama . ' ' . $field->label);
        if (str_contains($name, 'nik') || str_contains($name, 'npwpd')) return 'DEMO-' . strtoupper($field->nama) . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
        if (str_contains($name, 'opd') || str_contains($name, 'instansi')) return $opd->nama;
        if (str_contains($name, 'kecamatan') || str_contains($name, 'wilayah')) return ['Tahuna', 'Tabukan Utara', 'Manganitu', 'Tamako'][$index % 4];
        if (str_contains($name, 'tahun') || str_contains($name, 'periode')) return (string) (2023 + ($index % 4));
        return 'Contoh ' . ($index + 1);
    }
}
