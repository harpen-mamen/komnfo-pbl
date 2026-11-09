<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1: Data Kendala (Prinsip 9, 21)
 *
 * Implementasi dari flow:
 *   "Jika data tidak ditemukan, LAPORKAN, bukan buat master baru"
 *
 * Admin Desa yang tidak menemukan KK/Penduduk di master data akan
 * membuat laporan kendala, yang kemudian diteruskan ke OPD terkait
 * (Dukcapil untuk penduduk, dll).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('data_kendalas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('desa_id')->constrained('desas')->restrictOnDelete();
            $table->foreignId('dusun_id')->nullable()->constrained('dusuns')->nullOnDelete();

            $table->enum('jenis_kendala', [
                'penduduk_tidak_ditemukan',
                'kk_tidak_ditemukan',
                'data_tidak_lengkap',
                'data_tidak_konsisten',
                'foto_tidak_bisa_diambil',
                'koordinat_tidak_terbaca',
                'lainnya',
            ]);

            // Data yang dilaporkan (jika ada)
            $table->string('nik', 16)->nullable();
            $table->string('nomor_kk', 16)->nullable();
            $table->string('nama_lengkap', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();

            // Lokasi pelaporan
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('foto_path', 255)->nullable();

            // Detail laporan
            $table->text('deskripsi');
            $table->enum('status', [
                'baru',
                'diteruskan',
                'diverifikasi',
                'selesai',
                'ditolak',
            ])->default('baru');

            // Routing
            $table->foreignId('diteruskan_ke_opd_id')->nullable()->constrained('opds')->nullOnDelete();
            $table->timestamp('diteruskan_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('response_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis_kendala', 'status'], 'data_kendalas_jenis_status_idx');
            $table->index(['reported_by'], 'data_kendalas_reporter_idx');
            $table->index(['desa_id', 'status'], 'data_kendalas_desa_status_idx');
            $table->index(['diteruskan_ke_opd_id', 'status'], 'data_kendalas_opd_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_kendalas');
    }
};
