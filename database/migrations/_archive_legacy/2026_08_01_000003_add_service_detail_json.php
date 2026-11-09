<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('data_kesehatan_bulanans', function (Blueprint $t) { if (!Schema::hasColumn('data_kesehatan_bulanans', 'detail_pelayanan')) { $t->json('detail_pelayanan')->nullable(); } });
    } public function down(): void
    {
    }
};
