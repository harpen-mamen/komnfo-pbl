<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('submission_batches', 'module')) {
                $table->string('module', 32)->nullable()->after('id');
            }

            if (! Schema::hasColumn('submission_batches', 'periode_bulan')) {
                $table->unsignedTinyInteger('periode_bulan')->nullable()->after('kecamatan_id');
            }

            if (! Schema::hasColumn('submission_batches', 'periode_tahun')) {
                $table->unsignedSmallInteger('periode_tahun')->nullable()->after('semester');
            }

            $table->index(['module', 'kecamatan_id', 'status'], 'submission_batches_module_status_idx');
            $table->index(['module', 'kecamatan_id', 'periode_tahun', 'periode_bulan', 'semester'], 'submission_batches_module_period_idx');
        });

        if (Schema::hasColumn('submission_batches', 'tahun') && Schema::hasColumn('submission_batches', 'periode_tahun')) {
            DB::table('submission_batches')
                ->whereNull('periode_tahun')
                ->update(['periode_tahun' => DB::raw('tahun')]);
        }
    }

    public function down(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropIndex('submission_batches_module_status_idx');
            $table->dropIndex('submission_batches_module_period_idx');
            if (Schema::hasColumn('submission_batches', 'module')) {
                $table->dropColumn('module');
            }
            if (Schema::hasColumn('submission_batches', 'periode_bulan')) {
                $table->dropColumn('periode_bulan');
            }
            if (Schema::hasColumn('submission_batches', 'periode_tahun')) {
                $table->dropColumn('periode_tahun');
            }
        });
    }
};
