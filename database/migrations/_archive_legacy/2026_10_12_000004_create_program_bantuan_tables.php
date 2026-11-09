<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1: Program Bantuan (Prinsip 6)
 *
 * Data bantuan adalah domain tersendiri. Yang menerima bantuan
 * adalah INDIVIDU atau KELUARGA, bukan rumah.
 *
 * Design:
 * - program_bantuans: master program (BLT, PKH, dll)
 * - penerima_bantuans: polymorphic ke Penduduk ATAU Keluarga
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('program_bantuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
            $table->string('kode', 64);
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['uang', 'sembako', 'layanan', 'voucher', 'lainnya'])->default('uang');
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('satuan', 32)->nullable()->comment('bulan, tahun, kali, paket');
            $table->enum('periode_pemberian', ['sekali', 'bulanan', 'triwulanan', 'semesteran', 'tahunan'])->default('bulanan');
            $table->year('tahun_mulai')->nullable();
            $table->year('tahun_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('syarat')->nullable()->comment('kriteria penerima: kemiskinan, kategori, dll');
            $table->foreignId('source_attribution_id')->nullable()->constrained('source_attributions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['opd_id', 'kode'], 'program_bantuans_opd_kode_unique');
            $table->index(['is_active', 'jenis'], 'program_bantuans_active_jenis_idx');
        });

        Schema::create('penerima_bantuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_bantuan_id')->constrained('program_bantuans')->cascadeOnDelete();

            // Polymorphic ke Penduduk ATAU Keluarga
            $table->string('penerima_type', 32)->comment('penduduk|keluarga');
            $table->unsignedBigInteger('penerima_id');

            // Periode
            $table->year('tahun');
            $table->string('periode_label', 32)->nullable()->comment('Jan, Q1, Semester 1, dll');
            $table->foreignId('periode_id')->nullable()->constrained('periode_pelaporans')->nullOnDelete();

            $table->enum('status', ['aktif', 'nonaktif', 'selesai', 'ditolak'])->default('aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('nominal_diterima', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->json('dokumen_pendukung')->nullable();

            $table->foreignId('source_attribution_id')->nullable()->constrained('source_attributions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['penerima_type', 'penerima_id'], 'penerima_bantuans_penerima_idx');
            $table->index(['program_bantuan_id', 'tahun', 'status'], 'penerima_bantuans_program_periode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerima_bantuans');
        Schema::dropIfExists('program_bantuans');
    }
};
