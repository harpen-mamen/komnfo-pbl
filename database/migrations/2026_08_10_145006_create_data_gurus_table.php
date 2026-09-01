<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data guru & tenaga pendidikan per sekolah per semester.
     * Based on PUSKESMAS_SEKOLAH_DESIGN.md - Modul 2: Data Guru & Tenaga Pendidikan.
     */
    public function up(): void
    {
        Schema::create('data_gurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id');
            $table->unsignedBigInteger('submission_batch_id')->nullable();
            $table->string('tahun_ajaran', 9)->comment('Format: 2025/2026');
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->unsignedInteger('guru_total')->default(0);
            $table->unsignedInteger('guru_laki')->default(0);
            $table->unsignedInteger('guru_perempuan')->default(0);
            $table->unsignedInteger('guru_PNS')->default(0);
            $table->unsignedInteger('guru_PPPK')->default(0);
            $table->unsignedInteger('guru_Honorer')->default(0);
            $table->unsignedInteger('guru_S1_keatas')->default(0);
            $table->unsignedInteger('guru_S2_keatas')->default(0);
            $table->unsignedInteger('guru_sertifikasi')->default(0);
            $table->unsignedInteger('guru_belum_sertifikasi')->default(0);
            $table->unsignedInteger('GTK_Diklat_12_jam')->default(0);
            $table->unsignedInteger('GTK_Diklat_24_jam')->default(0);
            $table->unsignedInteger('tenaga_administrasi')->default(0);
            $table->unsignedInteger('tenaga_perpustakaan')->default(0);
            $table->unsignedInteger('tenaga_lab')->default(0);
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sekolah_id', 'tahun_ajaran', 'semester'], 'data_guru_unique');
            $table->foreign('sekolah_id')->references('id')->on('sekolahs')->cascadeOnDelete();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['submission_batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_gurus');
    }
};
