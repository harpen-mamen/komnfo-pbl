<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->string('sekolah');
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->unsignedInteger('jumlah_siswa')->default(0);
            $table->unsignedInteger('laki_laki')->default(0);
            $table->unsignedInteger('perempuan')->default(0);
            $table->unsignedInteger('putus_sekolah')->default(0);
            $table->unsignedInteger('lulus')->default(0);
            $table->unsignedInteger('naik_kelas')->default(0);
            $table->unsignedInteger('tidak_naik')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('status')->default('Terkirim');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['periode_tahun', 'periode_bulan', 'kecamatan_id'], 'pendidikan_bulanan_period_scope_idx');
            $table->index(['created_by', 'periode_tahun', 'periode_bulan'], 'pendidikan_bulanan_owner_period_idx');
        });

        Schema::create('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->string('puskesmas');
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->foreignId('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->unsignedInteger('jumlah_balita')->default(0);
            $table->unsignedInteger('jumlah_ibu_hamil')->default(0);
            $table->unsignedInteger('stunting')->default(0);
            $table->unsignedInteger('gizi_buruk')->default(0);
            $table->unsignedInteger('disabilitas')->default(0);
            $table->unsignedInteger('kematian')->default(0);
            $table->unsignedInteger('kelahiran')->default(0);
            $table->unsignedInteger('bpjs_aktif')->default(0);
            $table->unsignedInteger('bpjs_tidak_aktif')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('status')->default('Terkirim');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['periode_tahun', 'periode_bulan', 'kecamatan_id'], 'kesehatan_bulanan_period_scope_idx');
            $table->index(['created_by', 'periode_tahun', 'periode_bulan'], 'kesehatan_bulanan_owner_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_kesehatan_bulanans');
        Schema::dropIfExists('data_pendidikan_bulanans');
    }
};
