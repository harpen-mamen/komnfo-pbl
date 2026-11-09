<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->string('package_signature', 191)->nullable()->after('tahun');
            $table->json('package_keys')->nullable()->after('package_signature');
            $table->json('package_summary')->nullable()->after('package_keys');
            $table->index(['kecamatan_id', 'semester', 'tahun', 'package_signature'], 'submission_batches_package_scope_idx');
        });

        Schema::table('submission_batch_items', function (Blueprint $table) {
            $table->string('package_key', 64)->nullable()->after('submission_batch_id');
            $table->index(['submission_batch_id', 'package_key'], 'submission_batch_items_package_idx');
        });
    }

    public function down(): void
    {
        Schema::table('submission_batch_items', function (Blueprint $table) {
            $table->dropIndex('submission_batch_items_package_idx');
            $table->dropColumn('package_key');
        });

        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropIndex('submission_batches_package_scope_idx');
            $table->dropColumn(['package_signature', 'package_keys', 'package_summary']);
        });
    }
};
