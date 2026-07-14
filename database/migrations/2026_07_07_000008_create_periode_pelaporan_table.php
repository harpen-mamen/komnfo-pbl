<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_pelaporans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status')->default('Draft');
            $table->timestamps();
            $table->index(['status', 'tahun', 'bulan'], 'periode_pelaporan_status_period_idx');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->foreignId('periode_pelaporan_id')->nullable()->after('id')->constrained('periode_pelaporans')->nullOnDelete();
            $table->index('periode_pelaporan_id', 'pendidikan_bulanan_periode_lookup_idx');
        });

        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->foreignId('periode_pelaporan_id')->nullable()->after('id')->constrained('periode_pelaporans')->nullOnDelete();
            $table->index('periode_pelaporan_id', 'kesehatan_bulanan_periode_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->dropIndex('kesehatan_bulanan_periode_lookup_idx');
            $table->dropConstrainedForeignId('periode_pelaporan_id');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->dropIndex('pendidikan_bulanan_periode_lookup_idx');
            $table->dropConstrainedForeignId('periode_pelaporan_id');
        });

        Schema::dropIfExists('periode_pelaporans');
    }
};
