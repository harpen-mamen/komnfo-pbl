<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REFACTOR SDI: Tambah field metadata sesuai Perpres 39/2019 ke tabel `datasets`
 *
 * Standar metadata SDI:
 *   - judul, deskripsi, kontak_produsen, periode_update,
 *     cakupan_wilayah, bahasa, format_data, frekuensi_update,
 *     status_aktif, lisensi, sumber_data, klasifikasi_standar,
 *     tags, url_akses, tanggal_dibuat, tanggal_diubah
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            // SDI metadata - tambahan sesuai Perpres 39/2019
            if (! Schema::hasColumn('datasets', 'kontak_produsen')) {
                $table->string('kontak_produsen')->nullable()->after('config');
            }
            if (! Schema::hasColumn('datasets', 'cakupan_wilayah')) {
                $table->enum('cakupan_wilayah', ['kabupaten', 'kecamatan', 'desa'])
                    ->default('kabupaten')->after('kontak_produsen');
            }
            if (! Schema::hasColumn('datasets', 'bahasa')) {
                $table->enum('bahasa', ['id', 'en', 'both'])->default('id')->after('cakupan_wilayah');
            }
            if (! Schema::hasColumn('datasets', 'format_data')) {
                $table->string('format_data', 64)->default('JSON, CSV, XLSX')
                    ->after('bahasa');
            }
            if (! Schema::hasColumn('datasets', 'lisensi')) {
                $table->enum('lisensi', ['cc_by_4', 'open_data', 'terbatas', 'internal'])
                    ->default('cc_by_4')->after('format_data');
            }
            if (! Schema::hasColumn('datasets', 'sumber_data')) {
                $table->string('sumber_data', 64)->nullable()
                    ->comment('Survei/Administrasi/Sensor/API/Reguler')
                    ->after('lisensi');
            }
            if (! Schema::hasColumn('datasets', 'klasifikasi_standar')) {
                $table->string('klasifikasi_standar', 128)->nullable()
                    ->comment('mis. KBLI, ICD-10, KBJI, Kodefikasi Wilayah BPS')
                    ->after('sumber_data');
            }
            if (! Schema::hasColumn('datasets', 'tags')) {
                $table->json('tags')->nullable()->after('klasifikasi_standar');
            }
            if (! Schema::hasColumn('datasets', 'url_akses')) {
                $table->string('url_akses')->nullable()->after('tags');
            }
            if (! Schema::hasColumn('datasets', 'status_aktif')) {
                $table->enum('status_aktif', ['aktif', 'arsip', 'eksperimental'])
                    ->default('aktif')->after('url_akses');
            }
            if (! Schema::hasColumn('datasets', 'pj_data_nama')) {
                $table->string('pj_data_nama')->nullable()->after('status_aktif');
            }
            if (! Schema::hasColumn('datasets', 'pj_data_nip')) {
                $table->string('pj_data_nip', 30)->nullable()->after('pj_data_nama');
            }

            // Index tambahan untuk pencarian
            $table->index(['status_aktif'], 'datasets_sdi_status_idx');
            $table->index(['lisensi'], 'datasets_sdi_lisensi_idx');
        });
    }

    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $cols = [
                'kontak_produsen', 'cakupan_wilayah', 'bahasa', 'format_data',
                'lisensi', 'sumber_data', 'klasifikasi_standar', 'tags',
                'url_akses', 'status_aktif', 'pj_data_nama', 'pj_data_nip',
            ];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('datasets', $c));
            if ($existing) {
                $table->dropColumn($existing);
            }
            $table->dropIndex('datasets_sdi_status_idx');
            $table->dropIndex('datasets_sdi_lisensi_idx');
        });
    }
};
