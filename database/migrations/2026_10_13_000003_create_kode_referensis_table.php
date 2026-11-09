<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SDI: Tabel Kode Referensi Bersama (Perpres 39/2019)
 *
 * Setiap dataset harus mengacu ke kode referensi standar nasional:
 *   - Kodefikasi Wilayah BPS
 *   - KBLI (Klasifikasi Baku Lapangan Usaha Indonesia)
 *   - ICD-10 (Penyakit)
 *   - ICD-9-CM (Tindakan Medis)
 *   - KBJI (Klasifikasi Baku Jabatan Indonesia)
 *   - Kode Satker
 *   - Kode OPD Kemendagri
 *   - Standar lainnya (SNI, UNESCO, dll)
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('kode_referensis', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 32)->comment('kode_wilayah|kbli|icd10|icd9|kbji|kode_satker|kode_opd|kdnp|kdji|lainnya');
            $table->string('kode', 32);
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('standar_sumber', 64)->nullable()->comment('BPS|BSSN|Kemenkes|BPS Kemendagri');
            $table->unsignedSmallInteger('level')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['jenis', 'kode'], 'kode_ref_jenis_kode_unique');
            $table->index(['jenis', 'parent_id'], 'kode_ref_parent_idx');
            $table->index(['aktif'], 'kode_ref_aktif_idx');
            $table->foreign('parent_id')->references('id')->on('kode_referensis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_referensis');
    }
};
