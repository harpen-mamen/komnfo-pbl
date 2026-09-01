<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A14: Composite indexes for public map queries + scope filtering performance.
 *
 * Before:
 * - Public map filtered `rumah.publik = true` with `WHERE desa_id = ?`
 * - UserScope `visibleTo()` calls Schema::hasColumn() per query (cached in memory)
 * - Some queries filter by kecamatan via through-relation (rumah.desa.kecamatan_id)
 *
 * After: Composite indexes speed up the most common query patterns.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Public map: filter publik=true AND has lat/long (for map markers)
        Schema::table('rumah', function (Blueprint $table) {
            $table->index(['publik', 'desa_id'], 'rumah_public_desa_idx');
            $table->index(['publik', 'latitude', 'longitude'], 'rumah_public_geo_idx');
        });

        // UserScope auto-detection: when looking through rumah relation for kecamatan filtering,
        // a composite index speeds up the join path Keluarga->Rumah->Desa
        Schema::table('rumah', function (Blueprint $table) {
            $table->index(['desa_id', 'id'], 'rumah_desa_id_idx');
        });

        // Penduduk with through-relation (keluarga.rumah.desa): composite for filtering kecamatan
        Schema::table('keluarga', function (Blueprint $table) {
            $table->index(['rumah_id', 'id'], 'keluarga_rumah_id_idx');
        });

        // OPD tables: scope by kecamatan + period for fast filtering
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'periode_bulan', 'periode_tahun'], 'dkb_kec_period_idx');
            $table->index(['created_by', 'kecamatan_id'], 'dkb_creator_kec_idx');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->index(['kecamatan_id', 'semester', 'periode_tahun'], 'dpb_kec_semester_idx');
            $table->index(['created_by', 'kecamatan_id'], 'dpb_creator_kec_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rumah', function (Blueprint $table) {
            $table->dropIndex('rumah_public_desa_idx');
            $table->dropIndex('rumah_public_geo_idx');
            $table->dropIndex('rumah_desa_id_idx');
        });

        Schema::table('keluarga', function (Blueprint $table) {
            $table->dropIndex('keluarga_rumah_id_idx');
        });

        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->dropIndex('dkb_kec_period_idx');
            $table->dropIndex('dkb_creator_kec_idx');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->dropIndex('dpb_kec_semester_idx');
            $table->dropIndex('dpb_creator_kec_idx');
        });
    }
};
