<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Master data fasilitas kesehatan (Puskesmas, Pustu, Posyandu, Klinik).
     * Based on PUSKESMAS_SEKOLAH_DESIGN.md.
     */
    public function up(): void
    {
        Schema::create('fasilitas_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kecamatan_id');
            $table->unsignedBigInteger('desa_id')->nullable();
            $table->string('nama', 255);
            $table->string('kode_faskes', 50)->unique()->comment('Kode faskes Kemenkes');
            $table->enum('jenis', ['Pusat Pembantu', 'Puskesmas', 'Posyandu', 'Klinik'])->default('Pusat Pembantu');
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('kepala_puskes', 255)->nullable();
            $table->string('nip_kepala', 50)->nullable();
            $table->year('tahun_berdiri')->nullable();
            $table->boolean('status_operasional')->default(true);
            $table->decimal('koordinat_lat', 10, 7)->nullable();
            $table->decimal('koordinat_lng', 10, 7)->nullable();
            $table->unsignedInteger('jumlah_posyandu')->default(0);
            $table->unsignedInteger('jumlah_pustu')->default(0);
            $table->unsignedInteger('jumlah_kader')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kecamatan_id')->references('id')->on('kecamatans')->cascadeOnDelete();
            $table->foreign('desa_id')->references('id')->on('desas')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['kecamatan_id', 'jenis']);
            $table->index(['status_operasional']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas_kesehatans');
    }
};
