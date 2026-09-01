<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data siswa per sekolah per semester.
     * Based on PUSKESMAS_SEKOLAH_DESIGN.md - Modul 1: Data Siswa.
     */
    public function up(): void
    {
        Schema::create('data_siswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id');
            $table->unsignedBigInteger('submission_batch_id')->nullable();
            $table->string('tahun_ajaran', 9)->comment('Format: 2025/2026');
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->unsignedInteger('jumlah_siswa_total')->default(0);
            $table->unsignedInteger('laki_laki')->default(0);
            $table->unsignedInteger('perempuan')->default(0);
            $table->unsignedInteger('siswa_diterima_baru')->default(0);
            $table->unsignedInteger('siswa_lulus')->default(0);
            $table->unsignedInteger('putus_sekolah')->default(0);
            $table->unsignedInteger('naik_kelas')->default(0);
            $table->unsignedInteger('tidak_naik')->default(0);
            $table->unsignedInteger('siswa_mutasi_keluar')->default(0);
            $table->unsignedInteger('siswa_mutasi_datang')->default(0);
            $table->unsignedInteger('jumlah_rombel')->default(0);
            $table->unsignedInteger('siswa_ABK')->default(0);
            $table->unsignedInteger('siswa_BOS')->default(0);
            $table->unsignedInteger('siswa_PIP')->default(0);
            $table->unsignedInteger('siswa_KIP')->default(0);
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sekolah_id', 'tahun_ajaran', 'semester'], 'data_siswa_unique');
            $table->foreign('sekolah_id')->references('id')->on('sekolahs')->cascadeOnDelete();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['submission_batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_siswas');
    }
};
