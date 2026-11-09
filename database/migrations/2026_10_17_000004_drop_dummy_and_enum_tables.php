<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Tabel ini berisi data dummy (bukan data Sangihe asli).
        // Hapus tabel, data akan di-seed ulang dengan data SANGIHE asli.

        // Tabel master statis yang sudah di-enum di model
        $enumTables = [
            "workflow_status",  // Master status, sudah enum di SubmissionBatch
            "objek_lapangan_types",  // Master tipe, sudah jadi dataset.geometry_type
        ];
        foreach ($enumTables as $t) {
            if (Schema::hasTable($t)) {
                Schema::dropIfExists($t);
                echo "[DROPPED] Tabel enum \"$t\" berhasil dihapus.
";
            }
        }

        // Tabel wilayah dummy (akan di-seed ulang dengan data Sangihe)
        $wilayahDummyTables = ["kabupatens", "kecamatans", "desas"];
        foreach ($wilayahDummyTables as $t) {
            if (Schema::hasTable($t)) {
                // Hapus FK constraints yang mungkin reference ke tabel ini
                try {
                    DB::table($t)->delete();
                    Schema::dropIfExists($t);
                    echo "[DROPPED] Tabel wilayah dummy \"$t\" berhasil dihapus.
";
                } catch (\Throwable $e) {
                    echo "[ERROR] Gagal hapus $t: " . $e->getMessage() . "
";
                }
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // one-way
    }
};
