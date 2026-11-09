<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('rumah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desas')->cascadeOnDelete();
            $table->string('kode_rumah')->unique();
            $table->text('alamat_lengkap');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('status_rumah', ['Milik Sendiri', 'Kontrak', 'Sewa', 'Dinas', 'Lainnya']);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['desa_id', 'kode_rumah']);
        });

        Schema::create('keluarga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_id')->constrained('rumah')->cascadeOnDelete();
            $table->string('nomor_kk', 32)->unique();
            $table->string('nama_kepala_keluarga');
            $table->unsignedInteger('jumlah_anggota')->default(0);
            $table->enum('status_ekonomi', ['Miskin', 'Menengah', 'Mampu']);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('penduduk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluarga')->cascadeOnDelete();
            $table->string('nik', 32)->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu']);
            $table->enum('status_perkawinan', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']);
            $table->enum('pendidikan', ['Tidak Sekolah', 'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3']);
            $table->string('pekerjaan')->nullable();
            $table->decimal('penghasilan', 15, 2)->nullable();
            $table->enum('status_dalam_keluarga', ['Kepala Keluarga', 'Istri', 'Anak', 'Menantu', 'Cucu', 'Orang Tua', 'Lainnya']);
            $table->string('nomor_hp', 32)->nullable();
            $table->enum('status_penduduk', ['Tetap', 'Domisili']);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['keluarga_id', 'nama_lengkap']);
        });

        Schema::create('import_penduduk_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->string('nomor_kk', 32)->nullable();
            $table->string('nik', 32)->nullable();
            $table->string('nama')->nullable();
            $table->unsignedInteger('baris');
            $table->text('alasan');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_penduduk_logs');
        Schema::dropIfExists('penduduk');
        Schema::dropIfExists('keluarga');
        Schema::dropIfExists('rumah');
    }
};
