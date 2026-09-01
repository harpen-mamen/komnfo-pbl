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
        Schema::create('tenaga_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_kesehatan_bulanan_id')->nullable()->constrained('data_kesehatan_bulanans')->nullOnDelete();
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();
            $table->foreignId('penduduk_id')->nullable()->constrained('penduduk')->nullOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Data Tenaga Kesehatan
            $table->string('nama')->nullable();
            $table->string('nik', 16)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();

            // Profesi & Jenjang
            $table->enum('profesi', [
                'Dokter Umum',
                'Dokter Gigi',
                'Dokter Spesialis',
                'Perawat',
                'Bidan',
                'Apoteker',
                'Tenaga Farmasi',
                'Ahli Gizi',
                'Tenaga Kesehatan Masyarakat',
                'Sanitarian',
                'Analis Kesehatan',
                'Radiografer',
                'Fisioterapis',
                'Teknisi Elektromedis',
                'Lainnya'
            ])->nullable();

            $table->string('str')->nullable();
            $table->date('masa_berlaku_str')->nullable();
            $table->string('sip')->nullable();
            $table->date('masa_berlaku_sip')->nullable();

            // Tempat Kerja
            $table->string('tempat_kerja')->nullable();
            $table->enum('jenis_fasilitas', ['Puskesmas', 'Puskesmas Pembantu', 'Posyandu', 'Rumah Sakit', 'Klinik', 'Praktik Mandiri'])->nullable();
            $table->string('status_kepegawaian', 50)->nullable();

            // Status
            $table->enum('status_tenaga_kesehatan', ['Aktif', 'Mutasi', 'Cuti', 'Pensiun', 'Meninggal', 'Lainnya'])->default('Aktif');
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index(['kecamatan_id']);
            $table->index(['profesi']);
            $table->index(['jenis_fasilitas']);
            $table->index(['status_tenaga_kesehatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenaga_kesehatans');
    }
};
