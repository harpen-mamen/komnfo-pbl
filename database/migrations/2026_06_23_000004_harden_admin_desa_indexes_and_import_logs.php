<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('import_penduduk_logs') && ! Schema::hasTable('import_gagal')) {
            Schema::rename('import_penduduk_logs', 'import_gagal');
        }

        Schema::table('rumah', function (Blueprint $table) {
            $table->index('desa_id', 'rumah_desa_id_lookup_idx');
        });

        Schema::table('keluarga', function (Blueprint $table) {
            $table->index('rumah_id', 'keluarga_rumah_id_lookup_idx');
            $table->index('nomor_kk', 'keluarga_nomor_kk_lookup_idx');
        });

        Schema::table('penduduk', function (Blueprint $table) {
            $table->index('keluarga_id', 'penduduk_keluarga_id_lookup_idx');
            $table->index('nik', 'penduduk_nik_lookup_idx');
        });

        Schema::table('import_gagal', function (Blueprint $table) {
            $table->index('desa_id', 'import_gagal_desa_id_lookup_idx');
            $table->index('nomor_kk', 'import_gagal_nomor_kk_lookup_idx');
            $table->index('nik', 'import_gagal_nik_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('import_gagal', function (Blueprint $table) {
            $table->dropIndex('import_gagal_nik_lookup_idx');
            $table->dropIndex('import_gagal_nomor_kk_lookup_idx');
            $table->dropIndex('import_gagal_desa_id_lookup_idx');
        });

        Schema::table('penduduk', function (Blueprint $table) {
            $table->dropIndex('penduduk_nik_lookup_idx');
            $table->dropIndex('penduduk_keluarga_id_lookup_idx');
        });

        Schema::table('keluarga', function (Blueprint $table) {
            $table->dropIndex('keluarga_nomor_kk_lookup_idx');
            $table->dropIndex('keluarga_rumah_id_lookup_idx');
        });

        Schema::table('rumah', function (Blueprint $table) {
            $table->dropIndex('rumah_desa_id_lookup_idx');
        });

        if (Schema::hasTable('import_gagal') && ! Schema::hasTable('import_penduduk_logs')) {
            Schema::rename('import_gagal', 'import_penduduk_logs');
        }
    }
};
