<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Add unique constraint: one jenis per kecamatan-desa combination.
     * e.g., only one "Puskesmas" in the same village.
     */
    public function up(): void
    {
        Schema::table('fasilitas_kesehatans', function (Blueprint $table) {
            $table->unique(
                ['kecamatan_id', 'desa_id', 'jenis'],
                'fasilitas_kesehatans_unique_jenis'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas_kesehatans', function (Blueprint $table) {
            $table->dropUnique('fasilitas_kesehatans_unique_jenis');
        });
    }
};
