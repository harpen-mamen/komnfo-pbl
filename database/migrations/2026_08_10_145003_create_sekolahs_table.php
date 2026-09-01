<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master data sekolah (SD, SMP, SMA, SMK, SLB).
     * 1 admin mengelola SEMUA sekolah di 1 kecamatan.
     */
    public function up(): void
    {
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kecamatan_id');
            $table->unsignedBigInteger('desa_id')->nullable();
            $table->string('nama_sekolah', 255);
            $table->string('npsn', 20)->unique()->comment('NPSN Kemendikbud');
            $table->string('nss', 20)->nullable();
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'SMK', 'SLB']);
            $table->enum('status', ['Negeri', 'Swasta'])->default('Negeri');
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('kepala_sekolah', 255)->nullable();
            $table->string('nip_kepala', 50)->nullable();
            $table->year('tahun_berdiri')->nullable();
            $table->enum('akreditasi', ['A', 'B', 'C', 'Belum Terakreditasi'])->default('Belum Terakreditasi');
            $table->string('kurikulum', 50)->default('Kurikulum 2013');
            $table->decimal('koordinat_lat', 10, 7)->nullable();
            $table->decimal('koordinat_lng', 10, 7)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kecamatan_id')->references('id')->on('kecamatans')->cascadeOnDelete();
            $table->foreign('desa_id')->references('id')->on('desas')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['kecamatan_id', 'jenjang']);
            $table->index(['status']);
            $table->index(['akreditasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};
