<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('puskes_id')->nullable()->after('opd_id');
            $table->foreign('puskes_id')->references('id')->on('fasilitas_kesehatans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['puskes_id']);
            $table->dropColumn(['puskes_id']);
        });
    }
};
