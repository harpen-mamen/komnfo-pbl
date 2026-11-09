<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1: Source Attribution table
 *
 * Setiap data penting harus dapat diketahui sumbernya (Prinsip 16):
 * - source_type: tipe sumber data
 * - source_opd_id: OPD asal data
 * - source_user_id: user yang menginput
 * - source_dataset_id: dataset asal (jika ada)
 * - source_reference: referensi eksternal (SK, nomor keputusan, dll)
 * - source_metadata: metadata tambahan
 *
 * Polymorphic: dapat di-attach ke SEMUA entity (penduduk, keluarga,
 * rumah, dataset_value, objek_lapangan, dll).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('source_attributions', function (Blueprint $table) {
            $table->id();

            // Polymorphic
            $table->string('sourceable_type', 100);
            $table->unsignedBigInteger('sourceable_id');

            // Source identification
            $table->enum('source_type', [
                'dukcapil_import',     // Import dari Disdukcapil (master data)
                'dinas_master',        // Data master dari OPD
                'dinas_periodik',      // Laporan periodik OPD
                'puskes_periodik',     // Laporan periodik Puskesmas
                'sekolah_periodik',    // Laporan periodik Sekolah
                'field_collection',    // Pendataan lapangan (Admin Desa)
                'csr_program',         // Program CSR
                'bantuan_pemerintah',  // Bantuan pemerintah pusat/daerah
                'import_excel',        // Import Excel/CSV
                'api_eksternal',       // API eksternal
                'lainnya',
            ])->default('lainnya');

            $table->foreignId('source_opd_id')->nullable()->constrained('opds')->nullOnDelete();
            $table->foreignId('source_user_id')->nullable()->constrained('users')->nullOnDelete();

            // External reference (e.g., SK, nomor keputusan, batch import ID)
            $table->string('source_reference', 255)->nullable();
            $table->json('source_metadata')->nullable();

            $table->timestamps();

            $table->index(['sourceable_type', 'sourceable_id'], 'source_attributions_morph_idx');
            $table->index(['source_type'], 'source_attributions_type_idx');
            $table->index(['source_opd_id'], 'source_attributions_opd_idx');
            $table->index(['source_user_id'], 'source_attributions_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_attributions');
    }
};
