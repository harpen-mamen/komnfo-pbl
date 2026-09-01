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
        Schema::create('ibu_hamils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_kesehatan_bulanan_id')->nullable()->constrained('data_kesehatan_bulanans')->nullOnDelete();
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();
            $table->foreignId('penduduk_id')->nullable()->constrained('penduduk')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Data Ibu Hamil
            $table->string('nama')->nullable();
            $table->string('nik', 16)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nama_suami')->nullable();
            $table->string('no_telepon')->nullable();

            // Kehamilan
            $table->date('tanggal_hpl')->nullable();
            $table->integer('usia_kehamilan_minggu')->nullable();
            $table->enum('status_kehamilan', ['Hamil', 'Melahirkan', 'Abortus', 'Lainnya'])->default('Hamil');
            $table->date('tanggal_periksa_terakhir')->nullable();
            $table->string('frekuensi_periksa')->nullable();

            // Risiko
            $table->enum('kategori_resiko', ['Resiko Rendah', 'Resiko Tinggi'])->default('Resiko Rendah');
            $table->string('kondisi_resiko')->nullable();

            // Pelayanan
            $table->boolean('mendapat_fe')->default(false);
            $table->boolean('mendapat_imunisasi_tt')->default(false);
            $table->boolean('mendapat_konseling')->default(false);
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['kecamatan_id']);
            $table->index(['status_kehamilan']);
            $table->index(['kategori_resiko']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ibu_hamils');
    }
};
