<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable("dataset_fields")) {
            Schema::table("dataset_fields", function (Blueprint $table) {
                if (! Schema::hasColumn("dataset_fields", "konsep")) {
                    $table->text("konsep")->nullable()->after("label")
                        ->comment("Pasal 4 Perbup: Ide di balik data");
                }
                if (! Schema::hasColumn("dataset_fields", "definisi")) {
                    $table->text("definisi")->nullable()->after("konsep")
                        ->comment("Pasal 4 Perbup: Penjelasan batasan data");
                }
                if (! Schema::hasColumn("dataset_fields", "klasifikasi")) {
                    $table->string("klasifikasi", 200)->nullable()->after("definisi")
                        ->comment("Pasal 4 Perbup: Penggolongan data");
                }
                if (! Schema::hasColumn("dataset_fields", "ukuran")) {
                    $table->string("ukuran", 100)->nullable()->after("klasifikasi")
                        ->comment("Pasal 4 Perbup: Unit pengukuran");
                }
                if (! Schema::hasColumn("dataset_fields", "satuan")) {
                    $table->string("satuan", 50)->nullable()->after("ukuran")
                        ->comment("Pasal 4 Perbup: Satuan (Jiwa, Unit, Rp, Persen)");
                }
                if (! Schema::hasColumn("dataset_fields", "is_dimension")) {
                    $table->boolean("is_dimension")->default(false)->after("satuan");
                }
                if (! Schema::hasColumn("dataset_fields", "is_primary_key")) {
                    $table->boolean("is_primary_key")->default(false)->after("is_dimension");
                }
                if (! Schema::hasColumn("dataset_fields", "lookup_source")) {
                    $table->string("lookup_source", 100)->nullable();
                }
                $table->index("is_dimension", "dsf_dim_idx");
            });
        }

        if (Schema::hasTable("datasets")) {
            Schema::table("datasets", function (Blueprint $table) {
                if (! Schema::hasColumn("datasets", "category_id")) {
                    $table->foreignId("category_id")->nullable()->after("kategori")
                        ->constrained("dataset_categories")->nullOnDelete();
                }
                if (! Schema::hasColumn("datasets", "kode_sdi")) {
                    $table->string("kode_sdi", 32)->nullable()->after("kode")
                        ->comment("Kode Sektoral mis. 3.12");
                }
                if (! Schema::hasColumn("datasets", "walidata_id")) {
                    $table->foreignId("walidata_id")->nullable()->after("opd_id")
                        ->constrained("users")->nullOnDelete();
                }
                if (! Schema::hasColumn("datasets", "pembina_data")) {
                    $table->enum("pembina_data", ["bps", "dputr", "kominfo", "lainnya"])
                        ->default("kominfo")->after("walidata_id");
                }
                if (! Schema::hasColumn("datasets", "jadwal_rilis")) {
                    $table->date("jadwal_rilis")->nullable();
                }
                if (! Schema::hasColumn("datasets", "published_at")) {
                    $table->timestamp("published_at")->nullable();
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // one-way
    }
};
