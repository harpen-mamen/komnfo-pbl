<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentang_opd', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
            $table->string('nama_instansi', 200);
            $table->string('singkatan', 50)->nullable();
            $table->string('slug', 200)->unique();
            $table->enum('kategori', ['dinas', 'badan', 'kantor', 'sekretariat', 'rsud', 'lainnya'])->default('dinas');
            $table->string('logo', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->foreignId('kepala_instansi_id')->nullable()->constrained('pejabat_profiles')->nullOnDelete();
            $table->longText('profil_singkat')->nullable();
            $table->longText('tupoksi')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('tampilkan')->default(true);
            $table->timestamps();

            $table->index(['tampilkan', 'kategori', 'urutan'], 'topd_tampil_kat_urutan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentang_opd');
    }
};
