<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom workflow SDI ke dataset_values
 *
 * Sebelumnya DynamicDatasetResource reference kolom status_verifikasi dll.
 * yang belum ada. Sekarang ditambah agar sistem workflow dapat berjalan.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table("dataset_values", function (Blueprint $table) {
            // Status workflow (Perbup 14-23)
            if (! Schema::hasColumn("dataset_values", "status")) {
                $table->enum("status", [
                    "draft",
                    "submitted",
                    "verified",
                    "rejected",
                    "published",
                    "revision",
                ])->default("draft")->after("values");
            }

            // Alias untuk DynamicDatasetResource
            if (! Schema::hasColumn("dataset_values", "status_verifikasi")) {
                $table->string("status_verifikasi", 32)->nullable()->after("status");
            }

            if (! Schema::hasColumn("dataset_values", "verified_at")) {
                $table->timestamp("verified_at")->nullable()->after("status_verifikasi");
            }
            if (! Schema::hasColumn("dataset_values", "verified_by")) {
                $table->unsignedBigInteger("verified_by")->nullable()->after("verified_at");
            }
            if (! Schema::hasColumn("dataset_values", "published_at")) {
                $table->timestamp("published_at")->nullable()->after("verified_by");
            }
            if (! Schema::hasColumn("dataset_values", "notes")) {
                $table->text("notes")->nullable()->after("published_at");
            }

            // Indexes
            try {
                $table->index(["status"], "dv_status_idx");
                $table->index(["dataset_id", "status"], "dv_status_per_dataset_idx");
                $table->index(["published_at"], "dv_published_date_idx");
            } catch (\Throwable $e) {
                // skip
            }
        });

        // Hapus FK periode_id yang reference tabel yang sudah dihapus
        try {
            Schema::table("dataset_values", function (Blueprint $table) {
                $table->dropForeign(["periode_id"]);
            });
            echo "FK periode_id di-drop.
";
        } catch (\Throwable $e) {
            echo "FK periode_id tidak ada: " . $e->getMessage() . "
";
        }

        // Hapus kolom periode_id jika tabel master sudah tidak ada
        if (! Schema::hasTable("periode_pelaporans") && Schema::hasColumn("dataset_values", "periode_id")) {
            try {
                // SQLite (used for isolated feature tests) requires indexes on
                // the legacy column to be removed before dropping that column.
                foreach (Schema::getIndexes('dataset_values') as $index) {
                    if (in_array('periode_id', $index['columns'] ?? [], true) && ! ($index['primary'] ?? false)) {
                        Schema::table('dataset_values', function (Blueprint $table) use ($index): void {
                            $table->dropIndex($index['name']);
                        });
                    }
                }
                Schema::table("dataset_values", function (Blueprint $table) {
                    $table->dropColumn("periode_id");
                });
                echo "Kolom periode_id di-drop.
";
            } catch (\Throwable $e) {
                echo "Tidak bisa drop periode_id: " . $e->getMessage() . "
";
            }
        }
    }

    public function down(): void
    {
        // one-way
    }
};
