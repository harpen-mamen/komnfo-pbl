<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenaga_kesehatans', function (Blueprint $table) {
            $table->unsignedBigInteger('fasilitas_kesehatan_id')->nullable()->after('status_tenaga_kesehatan');
            $table->foreign('fasilitas_kesehatan_id')->references('id')->on('fasilitas_kesehatans')->nullOnDelete();
            $table->index(['fasilitas_kesehatan_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tenaga_kesehatans', function (Blueprint $table) {
            $table->dropForeign(['fasilitas_kesehatan_id']);
            $table->dropColumn(['fasilitas_kesehatan_id']);
        });
    }
};
