<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private const DATASET_CODE = 'DATA_KELUARGA_DESA';

    public function up(): void
    {
        $opdId = DB::table('opds')->where('kode', 'DUKCAPIL')->value('id');
        if (! $opdId) {
            throw new RuntimeException('OPD DUKCAPIL harus tersedia sebelum sumber keluarga mobile dibuat.');
        }

        $dataset = DB::table('datasets')
            ->where('kode', self::DATASET_CODE)
            ->where(function ($query) use ($opdId): void {
                $query->where('opd_id', $opdId)->orWhereNull('opd_id');
            })
            ->first();

        $config = json_decode($dataset->config ?? '{}', true);
        $config = is_array($config) ? $config : [];
        $config['mobile_family_source'] = true;

        $attributes = [
            'opd_id' => $opdId,
            'nama' => 'Data Keluarga per Desa',
            'deskripsi' => 'Daftar KK yang dikelola OPD Dukcapil per desa. Aplikasi mobile membaca data terbit untuk menautkan keluarga ke titik rumah; data KK tidak disalin ke kiriman mobile.',
            'kategori' => 'Kependudukan',
            'period_type' => 'master',
            'is_active' => true,
            'requires_verification' => true,
            'config' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'cakupan_wilayah' => 'desa',
            'bahasa' => 'id',
            'format_data' => 'JSON, CSV, XLSX',
            'lisensi' => 'terbatas',
            'sumber_data' => 'Administrasi',
            'status_aktif' => 'aktif',
            'updated_at' => now(),
        ];

        if ($dataset) {
            DB::table('datasets')->where('id', $dataset->id)->update($attributes);
            $datasetId = (int) $dataset->id;
        } else {
            $datasetId = (int) DB::table('datasets')->insertGetId([
                ...$attributes,
                'kode' => self::DATASET_CODE,
                'created_at' => now(),
            ]);
        }

        $fields = [
            ['nama' => 'nomor_kk', 'label' => 'Nomor Kartu Keluarga', 'tipe' => 'string', 'required' => true, 'urutan' => 0],
            ['nama' => 'nama_kepala_keluarga', 'label' => 'Nama Kepala Keluarga', 'tipe' => 'string', 'required' => true, 'urutan' => 1],
            ['nama' => 'jumlah_anggota', 'label' => 'Jumlah Anggota Keluarga', 'tipe' => 'integer', 'required' => false, 'urutan' => 2],
        ];

        foreach ($fields as $field) {
            $exists = DB::table('dataset_fields')
                ->where('dataset_id', $datasetId)
                ->where('nama', $field['nama'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('dataset_fields')->insert([
                'dataset_id' => $datasetId,
                ...$field,
                'is_searchable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Preserve family records if this definition has already been used.
    }
};
