<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('data_kesehatan_bulanans', 'kasus_penyakit')) {
            Schema::table('data_kesehatan_bulanans', function (Blueprint $table): void {
                $table->json('kasus_penyakit')->nullable()->after('gizi_buruk');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('data_kesehatan_bulanans', 'kasus_penyakit')) {
            Schema::table('data_kesehatan_bulanans', fn (Blueprint $table) => $table->dropColumn('kasus_penyakit'));
        }
    }
};
