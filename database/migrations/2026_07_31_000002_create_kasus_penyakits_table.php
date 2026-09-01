<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kasus_penyakits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('penduduk_id')->constrained('penduduk')->cascadeOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->foreignId('data_kesehatan_bulanan_id')->nullable()->constrained('data_kesehatan_bulanans')->nullOnDelete();
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();
            $table->string('jenis_penyakit');
            $table->date('tanggal_diagnosis')->nullable();
            $table->enum('status_kasus', ['aktif', 'sembuh', 'meninggal'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['kecamatan_id', 'jenis_penyakit']);
            $table->index(['desa_id', 'jenis_penyakit']);
        });
    }

    public function down(): void { Schema::dropIfExists('kasus_penyakits'); }
};
