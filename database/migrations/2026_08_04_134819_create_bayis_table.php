<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bayis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_kesehatan_bulanan_id')->nullable()->constrained('data_kesehatan_bulanans')->nullOnDelete();
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();
            $table->foreignId('penduduk_id')->nullable()->constrained('penduduk')->nullOnDelete();
            $table->foreignId('ibu_id')->nullable()->constrained('ibu_hamils')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Data Bayi
            $table->string('nama')->nullable();
            $table->string('nik', 16)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('alamat')->nullable();
            $table->integer('berat_badan_lahir')->nullable();
            $table->integer('panjang_badan_lahir')->nullable();

            // Imunisasi
            $table->boolean('imunisasi_hb0')->default(false);
            $table->boolean('imunisasi_bcg')->default(false);
            $table->boolean('imunisasi_dpt_hb1')->default(false);
            $table->boolean('imunisasi_dpt_hb2')->default(false);
            $table->boolean('imunisasi_dpt_hb3')->default(false);
            $table->boolean('imunisasi_polio1')->default(false);
            $table->boolean('imunisasi_polio2')->default(false);
            $table->boolean('imunisasi_polio3')->default(false);
            $table->boolean('imunisasi_polio4')->default(false);
            $table->boolean('imunisasi_campak')->default(false);
            $table->boolean('imunisasi_mmr')->default(false);

            // Status Gizi
            $table->enum('status_gizi', ['Baik', 'Kurang', 'Buruk', 'Lebih'])->default('Baik');
            $table->integer('berat_badan_sekarang')->nullable();
            $table->integer('panjang_badan_sekarang')->nullable();
            $table->enum('kategori_balita', ['Normal', 'Stunting', 'Wasting', 'Underweight'])->nullable();

            // Vitamin & Suplemen
            $table->boolean('mendapat_vitamin_a')->default(false);
            $table->date('tanggal_vitamin_a')->nullable();
            $table->boolean('mendapat_taburia')->default(false);

            // Kondisi Kesehatan
            $table->boolean('asi_eksklusif')->default(false);
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['kecamatan_id']);
            $table->index(['status_gizi']);
            $table->index(['kategori_balita']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayis');
    }
};
