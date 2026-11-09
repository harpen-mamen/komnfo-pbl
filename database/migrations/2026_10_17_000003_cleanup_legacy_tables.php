<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // data_values indexes
        if (Schema::hasTable("data_values")) {
            try {
                Schema::table("data_values", function (Blueprint $table) {
                    $table->index(["dataset_id", "tahun"], "dv_dataset_tahun_idx");
                    $table->index(["dataset_id", "status", "published_at"], "dv_published_idx");
                });
            } catch (\Throwable $e) {
                // skip
            }

            if (Schema::hasColumn("data_values", "wilayah_id")) {
                try {
                    Schema::table("data_values", function (Blueprint $table) {
                        $table->foreign("wilayah_id", "dv_wilayahs_fk")
                            ->references("id")->on("wilayahs")->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // skip
                }
            }
        }

        // workflow_logs compliance
        if (Schema::hasTable("workflow_logs")) {
            Schema::table("workflow_logs", function (Blueprint $table) {
                if (! Schema::hasColumn("workflow_logs", "workflow_stage")) {
                    $table->enum("workflow_stage", [
                        "plan", "collect", "verify", "publish",
                    ])->nullable()->after("to_status");
                }
                if (! Schema::hasColumn("workflow_logs", "data_value_id")) {
                    $table->unsignedBigInteger("data_value_id")->nullable()->after("batch_id");
                }
                if (! Schema::hasColumn("workflow_logs", "reason")) {
                    $table->text("reason")->nullable();
                }
            });
        }

        // Hapus tabel legacy 0-bar
        $legacyEmptyTables = [
            "dusuns", "import_gagal", "keluarga", "penduduk", "rumah",
            "rumah_keluarga", "tentang_opd", "tentang_sejarah",
            "tentang_visi_misi", "verification_comments",
            "kecamatans", "kabupatens", "desas",
            "objek_lapangans", "objek_lapangan_types",
            "periode_pelaporans", "workflow_status",
        ];

        foreach ($legacyEmptyTables as $t) {
            try {
                $count = DB::table($t)->count();
                if ($count == 0 && Schema::hasTable($t)) {
                    Schema::dropIfExists($t);
                    echo "[DROPPED] Tabel legacy \"$t\" (0 baris) berhasil dihapus.
";
                } else {
                    echo "[SKIPPED] Tabel \"$t\" (count=$count) tidak dihapus.
";
                }
            } catch (\Throwable $e) {
                // skip
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // one-way
    }
};
