<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pejabat_profiles', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe_pejabat', ['bupati', 'wakil_bupati', 'sekda', 'asisten', 'staf_ahli', 'kepala_dinas', 'lainnya']);
            $table->string('nama_lengkap', 200);
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->string('nip', 30)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('jabatan', 200);
            $table->string('foto', 255)->nullable();
            $table->text('biodata_singkat')->nullable();
            $table->json('riwayat_pendidikan')->nullable();
            $table->json('riwayat_jabatan')->nullable();
            $table->json('penghargaan')->nullable();
            $table->string('tanda_tangan', 255)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->integer('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tipe_pejabat', 'status', 'urutan'], 'pp_tipe_status_urutan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pejabat_profiles');
    }
};
