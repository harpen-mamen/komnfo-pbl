<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data prasarana & sarana sekolah.
     * Based on PUSKESMAS_SEKOLAH_DESIGN.md - Modul 3: Data Prasarana & Sarana.
     */
    public function up(): void
    {
        Schema::create('data_prasaranas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id');
            $table->unsignedBigInteger('submission_batch_id')->nullable();
            $table->string('tahun_ajaran', 9)->comment('Format: 2025/2026');
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->unsignedInteger('jumlah_ruang_kelas')->default(0);
            $table->unsignedInteger('kondisi_ruang_baik')->default(0);
            $table->unsignedInteger('rusak_ringan')->default(0);
            $table->unsignedInteger('rusak_berat')->default(0);
            $table->boolean('laboratorium_ipa')->default(false);
            $table->boolean('laboratorium_komputer')->default(false);
            $table->boolean('laboratorium_bahasa')->default(false);
            $table->boolean('perpustakaan')->default(false);
            $table->boolean('uks')->default(false);
            $table->boolean('musholla')->default(false);
            $table->boolean('sarana_olahraga')->default(false);
            $table->boolean('sumber_listrik')->default(false);
            $table->boolean('sumber_internet')->default(false);
            $table->enum('kondisi_gedung', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->default('Baik');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['sekolah_id', 'tahun_ajaran', 'semester'], 'data_prasaranas_unique');
            $table->foreign('sekolah_id')->references('id')->on('sekolahs')->cascadeOnDelete();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['submission_batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_prasaranas');
    }
};
