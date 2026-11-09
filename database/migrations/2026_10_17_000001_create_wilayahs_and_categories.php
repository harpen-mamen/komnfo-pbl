<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (! Schema::hasTable("dataset_categories")) {
            Schema::create("dataset_categories", function (Blueprint $table) {
                $table->id();
                $table->string("kode", 32)->unique()->comment("Sesuai UU 23/2014 + Perpres 39/2019");
                $table->string("nama", 200);
                $table->string("kelompok", 100)->nullable();
                $table->integer("urutan")->default(0);
                $table->text("deskripsi")->nullable();
                $table->boolean("aktif")->default(true);
                $table->timestamps();
                $table->index(["kelompok", "aktif"], "dcat_kelompok_idx");
            });
        }

        if (! Schema::hasTable("wilayahs")) {
            Schema::create("wilayahs", function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("parent_id")->nullable();
                $table->string("tipe", 20)->comment("kabupaten|kecamatan|desa|kampung|dusun");
                $table->string("kode_bps", 20)->nullable();
                $table->string("nama", 200);
                $table->string("singkatan", 50)->nullable();
                $table->string("kepala_wilayah", 200)->nullable();
                $table->string("latitude", 30)->nullable();
                $table->string("longitude", 30)->nullable();
                $table->json("metadata")->nullable();
                $table->boolean("aktif")->default(true);
                $table->timestamps();
                $table->softDeletes();
                $table->foreign("parent_id")->references("id")->on("wilayahs")->nullOnDelete();
                $table->unique(["parent_id", "nama"], "wilayahs_parent_nama_unique");
                $table->index(["tipe", "aktif"], "wilayahs_tipe_idx");
                $table->index("kode_bps", "wilayahs_kode_bps_idx");
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // one-way
    }
};
