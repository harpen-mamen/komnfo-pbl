<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekam medis per kunjungan di puskesmas (ICD-10/ICD-9).
     * Based on PUSKESMAS_SEKOLAH_DESIGN.md - Modul 1: Data Pelayanan Kesehatan.
     */
    public function up(): void
    {
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fasilitas_kesehatan_id');
            $table->unsignedBigInteger('penduduk_id')->nullable()->comment('Pasien');
            $table->unsignedBigInteger('tenaga_kesehatan_id')->nullable()->comment('Nakes penanggung jawab');
            $table->unsignedBigInteger('submission_batch_id')->nullable();
            $table->date('tanggal_kunjungan');
            $table->enum('jenis_pelayanan', ['Rawat Jalan', 'Rawat Inap', 'IGD', 'Promotif', 'Preventif'])->default('Rawat Jalan');
            $table->string('poli', 100)->nullable();
            $table->string('diagnosa_utama', 255)->comment('ICD-10');
            $table->string('kode_icd10', 20)->nullable();
            $table->json('diagnosa_sekunder')->nullable();
            $table->json('tindakan')->nullable()->comment('ICD-9');
            $table->json('obat_diberikan')->nullable();
            $table->enum('cara_bayar', ['BPJS', 'Asuransi', 'Umum', 'Gratis'])->default('BPJS');
            $table->enum('rujukan', ['Tidak', 'RS Kabupaten', 'RS Provinsi', 'Pusat Kesehatan Lain'])->default('Tidak');
            $table->string('nomor_rujukan', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fasilitas_kesehatan_id')->references('id')->on('fasilitas_kesehatans')->cascadeOnDelete();
            $table->foreign('penduduk_id')->references('id')->on('penduduk')->nullOnDelete();
            $table->foreign('tenaga_kesehatan_id')->references('id')->on('tenaga_kesehatans')->nullOnDelete();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['fasilitas_kesehatan_id', 'tanggal_kunjungan']);
            $table->index(['penduduk_id']);
            $table->index(['submission_batch_id']);
            $table->index(['kode_icd10']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
