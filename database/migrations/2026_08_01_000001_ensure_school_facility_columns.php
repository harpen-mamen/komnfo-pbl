<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('data_pendidikan_bulanans', function (Blueprint $t) {
            foreach (['tahun_ajaran','semester','npsn','status_sekolah','jenjang'] as $column) if (!Schema::hasColumn('data_pendidikan_bulanans',$column)) $t->string($column)->nullable();
            foreach (['rombel','siswa_baru','siswa_lulus','guru_asn','guru_pppk','guru_honorer','tenaga_administrasi','kepala_sekolah','jumlah_ruang','rusak_ringan','rusak_berat'] as $column) if (!Schema::hasColumn('data_pendidikan_bulanans',$column)) $t->unsignedInteger($column)->default(0);
            foreach (['internet','listrik','air_bersih','perpustakaan','laboratorium','uks'] as $column) if (!Schema::hasColumn('data_pendidikan_bulanans',$column)) $t->boolean($column)->default(false);
        });
    }
    public function down(): void {}
};
