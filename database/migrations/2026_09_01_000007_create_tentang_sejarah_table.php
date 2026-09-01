<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentang_sejarah', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 10);
            $table->string('judul_peristiwa', 200);
            $table->text('deskripsi');
            $table->string('gambar', 255)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('tampilkan')->default(true);
            $table->timestamps();

            $table->index(['tampilkan', 'urutan', 'tahun'], 'tsejarah_tampil_urutan_tahun_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentang_sejarah');
    }
};
