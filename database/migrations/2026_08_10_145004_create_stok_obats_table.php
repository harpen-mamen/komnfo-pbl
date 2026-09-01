<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stok obat & alkes di puskesmas.
     * Based on PUSKESMAS_SEKOLAH_DESIGN.md - Modul 5: Stok Obat & Alkes.
     */
    public function up(): void
    {
        Schema::create('stok_obats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fasilitas_kesehatan_id');
            $table->unsignedBigInteger('submission_batch_id')->nullable();
            $table->string('kode_obat', 50);
            $table->string('nama_obat', 255);
            $table->enum('satuan', ['Tablet', 'Kapsul', 'Sirup', 'Vial', 'Ampul', 'Botol', 'Pcs', 'Lembar', 'Set'])->default('Tablet');
            $table->enum('kategori', ['Obat Keras', 'Obat Bebas', 'Obat Bebas Terbatas', 'BMHP', 'Alat Kesehatan'])->default('Obat Bebas');
            $table->unsignedInteger('stok_awal')->default(0);
            $table->unsignedInteger('terima_dari_gudang')->default(0);
            $table->unsignedInteger('jumlah_dipakai')->default(0);
            $table->unsignedInteger('stok_akhir')->default(0);
            $table->unsignedInteger('jumlah_rusak')->default(0);
            $table->unsignedInteger('jumlah_kadaluarsa')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fasilitas_kesehatan_id')->references('id')->on('fasilitas_kesehatans')->cascadeOnDelete();
            $table->foreign('submission_batch_id')->references('id')->on('submission_batches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['fasilitas_kesehatan_id']);
            $table->index(['kode_obat']);
            $table->index(['submission_batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_obats');
    }
};
