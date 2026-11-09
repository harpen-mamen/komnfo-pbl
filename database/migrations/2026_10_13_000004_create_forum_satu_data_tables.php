<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SDI: Struktur Forum Satu Data Kabupaten (FSDK)
 *
 * Berdasarkan Perpres 39/2019 + praktikum Perbup standar kabupaten:
 *
 *   PEMBINA       : Bupati
 *   PENGARAH      : Wakil Bupati
 *   PENANGGUNG JWAB: Sekretaris Daerah
 *   KETUA         : Kepala Bappelitbang (atau Kominfo)
 *   WAKIL KETUA   : Kepala Bagian yang menangani
 *   KOORDINATOR   : Kominfo (Bidang Statistik & Persandian)
 *   WALI DATA     : Pejabat Kominfo
 *   KOORDINATOR STATISTIK: Kominfo (Persandian & Statistik)
 *   ANGGOTA       : Semua Produsen Data (Kepala OPD)
 *   SEKRETARIAT   : Bappelitbang
 *
 * Tabel:
 *  - forum_strukturs: Anggota forum (per role)
 *  - forum_sk: Histori SK Bupati (bisa multiple, diarsipkan)
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('forum_satu_data', function (Blueprint $table) {
            $table->id();
            $table->string('no_sk', 64)->nullable()->comment('Nomor SK Bupati yang berlaku');
            $table->date('tanggal_sk')->nullable();
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->string('tentang', 255);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('file_sk_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('forum_strukturs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_satu_data_id')->constrained('forum_satu_data')->cascadeOnDelete();
            $table->enum('peran', [
                'pembina',          // Bupati
                'pengarah',         // Wakil Bupati
                'penanggung_jawab', // Sekda
                'ketua',            // Ka Bappelitbang
                'wakil_ketua',      // Ka Bagian
                'koordinator',      // Kominfo
                'wali_data',        // Pejabat Kominfo
                'koordinator_statistik',
                'sekretariat',      // Bappelitbang
                'anggota',          // Produsen Data (Kepala OPD)
            ]);
            $table->string('nama_pejabat');
            $table->string('nip', 30)->nullable();
            $table->string('jabatan_asal')->nullable();
            $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kontak', 64)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->index(['peran', 'is_aktif'], 'forum_struktur_peran_idx');
            $table->index(['opd_id'], 'forum_struktur_opd_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_strukturs');
        Schema::dropIfExists('forum_satu_data');
    }
};
