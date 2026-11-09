<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1: Generic Dataset System (Prinsip 13)
 *
 * Setiap OPD punya dataset yang berbeda (Tenaga Kesehatan, Stunting,
 * Luas Tanam, Produksi, dll). Daripada membuat 32 sistem berbeda,
 * gunakan SATU platform yang configurable:
 *
 * datasets
 *   +-- dataset_fields (kolom dinamis)
 *   +-- dataset_values (nilai time-series)
 *
 * DatasetValue terikat ke:
 * - dataset_id
 * - periode_id (periode pelaporan)
 * - wilayah (polymorphic, opsional)
 * - source_attribution (siapa yg input)
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
            $table->string('kode', 64);
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 64)->nullable()->comment('mis. tenaga_kesehatan, stok_obat');
            $table->enum('period_type', [
                'tahunan',
                'semesteran',
                'triwulanan',
                'bulanan',
                'harian',
                'real_time',
            ])->default('bulanan');
            $table->enum('geometry_type', ['none', 'point', 'linestring', 'polygon'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_verification')->default(true);
            $table->json('config')->nullable()->comment('Konfigurasi tambahan: validation, display, dll');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['opd_id', 'kode'], 'datasets_opd_kode_unique');
            $table->index(['is_active', 'kategori'], 'datasets_active_kategori_idx');
        });

        Schema::create('dataset_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('nama', 64)->comment('internal name, mis. jumlah_dokter');
            $table->string('label', 255)->comment('display name');
            $table->enum('tipe', [
                'string',
                'text',
                'integer',
                'decimal',
                'boolean',
                'date',
                'datetime',
                'json',
                'reference',
            ])->default('string');
            $table->string('reference_target', 255)->nullable()->comment('untuk tipe=reference, mis. penduduk, sekolah');
            $table->boolean('required')->default(false);
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable()->comment('untuk enum-like, mis. jenis_kelamin');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_searchable')->default(false);
            $table->timestamps();

            $table->unique(['dataset_id', 'nama'], 'dataset_fields_dataset_nama_unique');
            $table->index(['dataset_id', 'urutan'], 'dataset_fields_dataset_urutan_idx');
        });

        Schema::create('dataset_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();

            // Periode (optional - bisa null untuk data tanpa periode)
            // Period definitions were archived; this nullable legacy ID is kept as plain data.
            $table->foreignId('periode_id')->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('periode_label', 32)->nullable()->comment('mis. Jan, Q1, Semester 1, 2026');

            // Wilayah (polymorphic reference)
            $table->string('wilayah_type', 32)->nullable()->comment('kabupaten|kecamatan|desa');
            $table->unsignedBigInteger('wilayah_id')->nullable();

            // Data values
            $table->json('values')->comment('actual data keyed by dataset_fields.nama');

            // Source tracking
            // Source attribution tables are archived; retain the optional reference value only.
            $table->foreignId('source_attribution_id')->nullable();
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dataset_id', 'tahun', 'periode_label'], 'dataset_values_lookup_idx');
            $table->index(['wilayah_type', 'wilayah_id'], 'dataset_values_wilayah_idx');
            $table->index(['periode_id'], 'dataset_values_periode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataset_values');
        Schema::dropIfExists('dataset_fields');
        Schema::dropIfExists('datasets');
    }
};
