<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tentang_visi_misi', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['visi', 'misi', 'tujuan', 'sasaran']);
            $table->text('konten');
            $table->integer('urutan')->default(0);
            $table->string('periode', 20)->nullable();
            $table->timestamps();

            $table->index(['tipe', 'periode', 'urutan'], 'tvm_tipe_periode_urutan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentang_visi_misi');
    }
};
