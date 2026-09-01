<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SPEC-003: Add submission_batch_id FK to OPD tables.
     *
     * This ensures DataKesehatanBulanan and DataPendidikanBulanan records
     * are linked to their submission batch, enabling the approval workflow.
     *
     * Before: OPD data bypassed workflow approval entirely.
     * After:  Every OPD record belongs to a submission batch that must be approved.
     */
    public function up(): void
    {
        // Data Kesehatan Bulanan — add submission_batch_id FK
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->foreignId('submission_batch_id')
                ->nullable()
                ->after('kecamatan_id')
                ->constrained('submission_batches')
                ->nullOnDelete();

            // Composite index for efficient scope queries.
            $table->index(
                ['submission_batch_id', 'kecamatan_id', 'periode_bulan', 'periode_tahun'],
                'data_kesehatan_scope_idx'
            );
        });

        // Data Pendidikan Bulanan — add submission_batch_id FK
        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->foreignId('submission_batch_id')
                ->nullable()
                ->after('kecamatan_id')
                ->constrained('submission_batches')
                ->nullOnDelete();

            // Composite index for efficient scope queries.
            $table->index(
                ['submission_batch_id', 'kecamatan_id', 'semester', 'periode_tahun'],
                'data_pendidikan_scope_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('data_kesehatan_bulanans', function (Blueprint $table) {
            $table->dropIndex('data_kesehatan_scope_idx');
            $table->dropForeign(['submission_batch_id']);
            $table->dropColumn('submission_batch_id');
        });

        Schema::table('data_pendidikan_bulanans', function (Blueprint $table) {
            $table->dropIndex('data_pendidikan_scope_idx');
            $table->dropForeign(['submission_batch_id']);
            $table->dropColumn('submission_batch_id');
        });
    }
};