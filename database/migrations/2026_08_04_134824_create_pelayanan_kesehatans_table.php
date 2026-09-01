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
        Schema::create('pelayanan_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_kesehatan_bulanan_id')->nullable()->constrained('data_kesehatan_bulanans')->nullOnDelete();
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();
            $table->foreignId('penduduk_id')->nullable()->constrained('penduduk')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Data Pelayanan
            $table->string('nama_pasien')->nullable();
            $table->string('nik', 16)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();

            // Jenis Pelayanan
            $table->enum('jenis_pelayanan', [
                'Kunjungan_Rawat_Jalan',
                'Kunjungan_Rawat_Inap',
                'Kunjungan_Darurat',
                'Persalinan',
                'Pendaftaran_BPJS',
                'Rujukan',
                'Imunisasi',
                'Pemeriksaan_kehamilan',
                'Pelayanan_Gizi',
                'Pemeriksaan_Bayi_Balita',
                'Konseling_KB',
                'Lainnya'
            ])->nullable();

            // Detail Pelayanan
            $table->date('tanggal_pelayanan')->nullable();
            $table->string('nama_pelayanan')->nullable();
            $table->string('diagnosa')->nullable();
            $table->text('tindakan')->nullable();
            $table->string('tenaga_kesehatan_yang_melayani')->nullable();

            // Hasil
            $table->enum('hasil_pemeriksaan', ['Sehat', 'Sakit Ringan', 'Sakit Sedang', 'Sakit Berat', 'Rujuk'])->nullable();
            $table->string('resep')->nullable();
            $table->text('catatan')->nullable();

            // Untuk Imunisasi
            $table->string('jenis_imunisasi')->nullable();
            $table->date('tanggal_imunisasi')->nullable();

            $table->timestamps();

            $table->index(['kecamatan_id']);
            $table->index(['jenis_pelayanan']);
            $table->index(['tanggal_pelayanan']);
            $table->index(['hasil_pemeriksaan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelayanan_kesehatans');
    }
};
