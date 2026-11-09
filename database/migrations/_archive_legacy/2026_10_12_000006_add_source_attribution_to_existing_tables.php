<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1: Backward-compat - tambah source_attribution_id ke tabel existing
 *
 * Tujuan:
 * - Setiap data existing bisa di-attach ke source_attribution
 * - Backfill data existing dengan source yang sesuai:
 *   - penduduk, keluarga  -> source_type=dukcapil_import
 *   - rumah                -> source_type=field_collection
 *   - sekolah, puskes      -> source_type=dinas_master
 *   - data_kesehatan_*     -> source_type=dinas_periodik
 *   - data_pendidikan_*    -> source_type=dinas_periodik
 *
 * CATATAN: kolom ditambahkan sebagai NULLABLE agar TIDAK BREAK data existing.
 * Backfill dilakukan di migration terpisah.
 */
return new class () extends Migration {
    public function up(): void
    {
        // Master data
        Schema::table('penduduk', function (Blueprint $table) {
            if (!Schema::hasColumn('penduduk', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        Schema::table('keluarga', function (Blueprint $table) {
            if (!Schema::hasColumn('keluarga', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        // Field data
        Schema::table('rumah', function (Blueprint $table) {
            if (!Schema::hasColumn('rumah', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        // Sectoral - kesehatan
        Schema::table('fasilitas_kesehatans', function (Blueprint $table) {
            if (!Schema::hasColumn('fasilitas_kesehatans', 'source_attribution_id')) {
                if (Schema::hasColumn('fasilitas_kesehatans', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('stok_obats', function (Blueprint $table) {
            if (!Schema::hasColumn('stok_obats', 'source_attribution_id')) {
                if (Schema::hasColumn('stok_obats', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('rekam_medis', function (Blueprint $table) {
            if (!Schema::hasColumn('rekam_medis', 'source_attribution_id')) {
                if (Schema::hasColumn('rekam_medis', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('bayis', function (Blueprint $table) {
            if (!Schema::hasColumn('bayis', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        Schema::table('ibu_hamils', function (Blueprint $table) {
            if (!Schema::hasColumn('ibu_hamils', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        Schema::table('tenaga_kesehatans', function (Blueprint $table) {
            if (!Schema::hasColumn('tenaga_kesehatans', 'source_attribution_id')) {
                if (Schema::hasColumn('tenaga_kesehatans', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('pelayanan_kesehatans', function (Blueprint $table) {
            if (!Schema::hasColumn('pelayanan_kesehatans', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        Schema::table('kasus_penyakits', function (Blueprint $table) {
            if (!Schema::hasColumn('kasus_penyakits', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            if (!Schema::hasColumn('data_kesehatan_bulanans', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });

        // Sectoral - pendidikan
        Schema::table('sekolahs', function (Blueprint $table) {
            if (!Schema::hasColumn('sekolahs', 'source_attribution_id')) {
                if (Schema::hasColumn('sekolahs', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('data_gurus', function (Blueprint $table) {
            if (!Schema::hasColumn('data_gurus', 'source_attribution_id')) {
                if (Schema::hasColumn('data_gurus', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('data_siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('data_siswas', 'source_attribution_id')) {
                if (Schema::hasColumn('data_siswas', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('data_prasaranas', function (Blueprint $table) {
            if (!Schema::hasColumn('data_prasaranas', 'source_attribution_id')) {
                if (Schema::hasColumn('data_prasaranas', 'updated_by')) {
                    $table->foreignId('source_attribution_id')->nullable()->after('updated_by')->constrained('source_attributions')->nullOnDelete();
                } else {
                    $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
                }
            }
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            if (!Schema::hasColumn('data_pendidikan_bulanans', 'source_attribution_id')) {
                $table->foreignId('source_attribution_id')->nullable()->after('created_by')->constrained('source_attributions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        $tables = [
            'penduduk',
            'keluarga',
            'rumah',
            'fasilitas_kesehatans',
            'stok_obats',
            'rekam_medis',
            'bayis',
            'ibu_hamils',
            'tenaga_kesehatans',
            'pelayanan_kesehatans',
            'kasus_penyakits',
            'data_kesehatan_bulanans',
            'sekolahs',
            'data_gurus',
            'data_siswas',
            'data_prasaranas',
            'data_pendidikan_bulanans',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('source_attribution_id');
            });
        }
    }
};
