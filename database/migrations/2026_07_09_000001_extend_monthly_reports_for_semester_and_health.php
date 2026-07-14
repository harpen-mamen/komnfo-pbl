<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            if (! Schema::hasColumn('data_pendidikan_bulanans', 'semester')) {
                $table->string('semester')->nullable()->after('periode_pelaporan_id');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'npsn')) {
                $table->string('npsn')->nullable()->after('sekolah');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'status_sekolah')) {
                $table->string('status_sekolah')->nullable()->after('npsn');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'jenjang')) {
                $table->string('jenjang')->nullable()->after('status_sekolah');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'rombel')) {
                $table->unsignedInteger('rombel')->default(0)->after('jenjang');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'siswa_baru')) {
                $table->unsignedInteger('siswa_baru')->default(0)->after('jumlah_siswa');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'siswa_lulus')) {
                $table->unsignedInteger('siswa_lulus')->default(0)->after('putus_sekolah');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'siswa_pindah')) {
                $table->unsignedInteger('siswa_pindah')->default(0)->after('siswa_lulus');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'abk')) {
                $table->unsignedInteger('abk')->default(0)->after('siswa_pindah');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'guru_asn')) {
                $table->unsignedInteger('guru_asn')->default(0)->after('abk');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'guru_pppk')) {
                $table->unsignedInteger('guru_pppk')->default(0)->after('guru_asn');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'guru_honorer')) {
                $table->unsignedInteger('guru_honorer')->default(0)->after('guru_pppk');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'tenaga_administrasi')) {
                $table->unsignedInteger('tenaga_administrasi')->default(0)->after('guru_honorer');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'kepala_sekolah')) {
                $table->unsignedInteger('kepala_sekolah')->default(0)->after('tenaga_administrasi');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'internet')) {
                $table->boolean('internet')->default(false)->after('kepala_sekolah');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'listrik')) {
                $table->boolean('listrik')->default(false)->after('internet');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'air_bersih')) {
                $table->boolean('air_bersih')->default(false)->after('listrik');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'perpustakaan')) {
                $table->boolean('perpustakaan')->default(false)->after('air_bersih');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'laboratorium')) {
                $table->boolean('laboratorium')->default(false)->after('perpustakaan');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'uks')) {
                $table->boolean('uks')->default(false)->after('laboratorium');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'jumlah_ruang')) {
                $table->unsignedInteger('jumlah_ruang')->default(0)->after('uks');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'rusak_ringan')) {
                $table->unsignedInteger('rusak_ringan')->default(0)->after('jumlah_ruang');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'rusak_berat')) {
                $table->unsignedInteger('rusak_berat')->default(0)->after('rusak_ringan');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'program_pendidikan')) {
                $table->json('program_pendidikan')->nullable()->after('rusak_berat');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'prestasi')) {
                $table->json('prestasi')->nullable()->after('program_pendidikan');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'permasalahan')) {
                $table->json('permasalahan')->nullable()->after('prestasi');
            }

            if (! Schema::hasColumn('data_pendidikan_bulanans', 'uraian_permasalahan')) {
                $table->text('uraian_permasalahan')->nullable()->after('permasalahan');
            }
        });

        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            if (! Schema::hasColumn('data_kesehatan_bulanans', 'jenis')) {
                $table->string('jenis')->nullable()->after('puskesmas');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'kunjungan')) {
                $table->unsignedInteger('kunjungan')->default(0)->after('jenis');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'rawat_jalan')) {
                $table->unsignedInteger('rawat_jalan')->default(0)->after('kunjungan');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'rawat_inap')) {
                $table->unsignedInteger('rawat_inap')->default(0)->after('rawat_jalan');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'rujukan')) {
                $table->unsignedInteger('rujukan')->default(0)->after('rawat_inap');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'persalinan')) {
                $table->unsignedInteger('persalinan')->default(0)->after('rujukan');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'ibu_hamil')) {
                $table->unsignedInteger('ibu_hamil')->default(0)->after('persalinan');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'balita')) {
                $table->unsignedInteger('balita')->default(0)->after('ibu_hamil');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'bayi')) {
                $table->unsignedInteger('bayi')->default(0)->after('balita');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'imunisasi')) {
                $table->unsignedInteger('imunisasi')->default(0)->after('bayi');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'stunting')) {
                $table->unsignedInteger('stunting')->default(0)->after('imunisasi');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'gizi_buruk')) {
                $table->unsignedInteger('gizi_buruk')->default(0)->after('stunting');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'ispa')) {
                $table->unsignedInteger('ispa')->default(0)->after('gizi_buruk');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'dbd')) {
                $table->unsignedInteger('dbd')->default(0)->after('ispa');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'malaria')) {
                $table->unsignedInteger('malaria')->default(0)->after('dbd');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'tbc')) {
                $table->unsignedInteger('tbc')->default(0)->after('malaria');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'hipertensi')) {
                $table->unsignedInteger('hipertensi')->default(0)->after('tbc');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'diabetes')) {
                $table->unsignedInteger('diabetes')->default(0)->after('hipertensi');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'covid')) {
                $table->unsignedInteger('covid')->default(0)->after('diabetes');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'lainnya')) {
                $table->unsignedInteger('lainnya')->default(0)->after('covid');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'dokter')) {
                $table->unsignedInteger('dokter')->default(0)->after('lainnya');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'perawat')) {
                $table->unsignedInteger('perawat')->default(0)->after('dokter');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'bidan')) {
                $table->unsignedInteger('bidan')->default(0)->after('perawat');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'apoteker')) {
                $table->unsignedInteger('apoteker')->default(0)->after('bidan');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'ahli_gizi')) {
                $table->unsignedInteger('ahli_gizi')->default(0)->after('apoteker');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'ambulans')) {
                $table->boolean('ambulans')->default(false)->after('ahli_gizi');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'laboratorium')) {
                $table->boolean('laboratorium')->default(false)->after('ambulans');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'apotek')) {
                $table->boolean('apotek')->default(false)->after('laboratorium');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'ruang_bersalin')) {
                $table->boolean('ruang_bersalin')->default(false)->after('apotek');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'internet')) {
                $table->boolean('internet')->default(false)->after('ruang_bersalin');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'listrik')) {
                $table->boolean('listrik')->default(false)->after('internet');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'air_bersih')) {
                $table->boolean('air_bersih')->default(false)->after('listrik');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'status_obat')) {
                $table->string('status_obat')->nullable()->after('air_bersih');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'permasalahan')) {
                $table->json('permasalahan')->nullable()->after('status_obat');
            }

            if (! Schema::hasColumn('data_kesehatan_bulanans', 'uraian_permasalahan')) {
                $table->text('uraian_permasalahan')->nullable()->after('permasalahan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $columns = [
                'semester',
                'npsn',
                'status_sekolah',
                'jenjang',
                'rombel',
                'siswa_baru',
                'siswa_lulus',
                'siswa_pindah',
                'abk',
                'guru_asn',
                'guru_pppk',
                'guru_honorer',
                'tenaga_administrasi',
                'kepala_sekolah',
                'internet',
                'listrik',
                'air_bersih',
                'perpustakaan',
                'laboratorium',
                'uks',
                'jumlah_ruang',
                'rusak_ringan',
                'rusak_berat',
                'program_pendidikan',
                'prestasi',
                'permasalahan',
                'uraian_permasalahan',
            ];

            $columnsToDrop = array_filter($columns, fn (string $column) => Schema::hasColumn('data_pendidikan_bulanans', $column));

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $columns = [
                'jenis',
                'kunjungan',
                'rawat_jalan',
                'rawat_inap',
                'rujukan',
                'persalinan',
                'ibu_hamil',
                'balita',
                'bayi',
                'imunisasi',
                'stunting',
                'gizi_buruk',
                'ispa',
                'dbd',
                'malaria',
                'tbc',
                'hipertensi',
                'diabetes',
                'covid',
                'lainnya',
                'dokter',
                'perawat',
                'bidan',
                'apoteker',
                'ahli_gizi',
                'ambulans',
                'laboratorium',
                'apotek',
                'ruang_bersalin',
                'internet',
                'listrik',
                'air_bersih',
                'status_obat',
                'permasalahan',
                'uraian_permasalahan',
            ];

            $columnsToDrop = array_filter($columns, fn (string $column) => Schema::hasColumn('data_kesehatan_bulanans', $column));

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
