<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table): void {
            $table->enum('period_type', ['tahunan', 'semesteran', 'triwulanan', 'bulanan', 'harian', 'real_time', 'master', 'event'])
                ->default('bulanan')->change();
        });

        if (! Schema::hasColumn('forum_strukturs', 'urutan')) {
            Schema::table('forum_strukturs', function (Blueprint $table) {
                $table->unsignedInteger('urutan')->default(0)->after('is_aktif');
            });
        }
    }

    public function down(): void {}
};
