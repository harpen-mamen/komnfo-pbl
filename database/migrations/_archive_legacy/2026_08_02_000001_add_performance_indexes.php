<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Composite index for polymorphic relationship in submission_batch_items
        // Critical for scopeInApprovedBatch() performance
        Schema::table('submission_batch_items', function (Blueprint $table) {
            $table->index(['entity_type', 'entity_id'], 'sbi_entity_idx');
        });

        // Index for filtering submission batches by kecamatan and status
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'status'], 'sb_kecamatan_status_idx');
            $table->index(['kecamatan_id', 'module', 'status'], 'sb_kecamatan_module_status_idx');
        });

        // Index for data_kesehatan_bulanans queries
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'periode_bulan', 'periode_tahun'], 'dkb_period_idx');
        });

        // Index for data_pendidikan_bulanans queries
        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'periode_tahun', 'semester'], 'dpb_period_idx');
        });

        // Index for rumah queries by desa
        Schema::table('rumah', function (Blueprint $table) {
            $table->index(['desa_id', 'status_rumah'], 'rumah_desa_status_idx');
        });

        // Index for keluarga queries by rumah
        Schema::table('keluarga', function (Blueprint $table) {
            $table->index(['rumah_id', 'status_ekonomi'], 'keluarga_rumah_status_idx');
        });

        // Index for penduduk queries by keluarga
        Schema::table('penduduk', function (Blueprint $table) {
            $table->index(['keluarga_id', 'jenis_kelamin'], 'penduduk_keluarga_gender_idx');
            $table->index(['keluarga_id', 'pendidikan'], 'penduduk_keluarga_pendidikan_idx');
        });
    }

    public function down(): void
    {
        Schema::table('submission_batch_items', function (Blueprint $table) {
            $table->dropIndex('sbi_entity_idx');
        });

        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropIndex('sb_kecamatan_status_idx');
            $table->dropIndex('sb_kecamatan_module_status_idx');
        });

        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->dropIndex('dkb_period_idx');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->dropIndex('dpb_period_idx');
        });

        Schema::table('rumah', function (Blueprint $table) {
            $table->dropIndex('rumah_desa_status_idx');
        });

        Schema::table('keluarga', function (Blueprint $table) {
            $table->dropIndex('keluarga_rumah_status_idx');
        });

        Schema::table('penduduk', function (Blueprint $table) {
            $table->dropIndex('penduduk_keluarga_gender_idx');
            $table->dropIndex('penduduk_keluarga_pendidikan_idx');
        });
    }
};
