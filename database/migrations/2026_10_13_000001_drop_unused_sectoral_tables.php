<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * REFACTOR SDI: Hapus tabel sektoral hardcoded.
 *
 * Drop dilakukan dalam URUTAN TERBALIK dependency:
 *   1. Child tables dulu (yang reference ke legacy tables)
 *   2. Lalu parent tables (legacy tables)
 *   3. Lalu drop FK columns di users
 */
return new class () extends Migration {
    public function up(): void
    {
        // Disable FK checks sementara untuk drop yang kompleks
        Schema::disableForeignKeyConstraints();

        // TAHAP 1: Drop child tables yang reference ke legacy tables
        $childTables = [
            'stok_obats',           // -> fasilitas_kesehatans
            'rekam_medis',          // -> fasilitas_kesehatans, tenaga_kesehatans
            'data_prasaranas',      // -> sekolahs
            'data_siswas',          // -> sekolahs
            'data_gurus',           // -> sekolahs
            'tenaga_kesehatans',    // -> fasilitas_kesehatans
        ];
        foreach ($childTables as $t) {
            Schema::dropIfExists($t);
        }

        // TAHAP 2: Drop legacy sectoral tables
        $legacyTables = [
            'data_pendidikan_bulanans',
            'data_kesehatan_bulanans',
            'bayis',
            'ibu_hamils',
            'kasus_penyakits',
            'pelayanan_kesehatans',
            'sekolahs',
            'fasilitas_kesehatans',
            'rumahs',
            'penerima_bantuans',
            'program_bantuans',
        ];
        foreach ($legacyTables as $t) {
            Schema::dropIfExists($t);
        }

        // TAHAP 3: Drop legacy content tables
        $contentTables = [
            'tentang_visi_misis',
            'tentang_sejarahs',
            'tentang_opd_galleries',
            'tentang_opds',
            'about_section_media',
            'about_section_contents',
            'about_page_settings',
            'pejabat_profiles',
            'public_hero_settings',
            'public_news',
        ];
        foreach ($contentTables as $t) {
            Schema::dropIfExists($t);
        }

        // TAHAP 4: Drop legacy workflow tables
        $workflowTables = [
            'workflow_histories',
            'workflow_instances',
            'kecamatan_submissions',
        ];
        foreach ($workflowTables as $t) {
            Schema::dropIfExists($t);
        }

        // TAHAP 5: Drop legacy user & kendala tables
        $miscTables = [
            'user_registrations',
            'data_kendalas',
            'import_penduduk_logs',
            'source_attributions',
        ];
        foreach ($miscTables as $t) {
            Schema::dropIfExists($t);
        }

        // TAHAP 6: Drop FK columns di users
        if (Schema::hasColumn('users', 'puskes_id')) {
            Schema::table('users', function ($table) {
                $table->dropForeign(['puskes_id']);
                $table->dropColumn('puskes_id');
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // one-way refactor
    }
};
