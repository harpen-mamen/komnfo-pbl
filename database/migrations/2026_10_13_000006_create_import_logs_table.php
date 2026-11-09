<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REFACTOR: Gabung import_penduduk_logs + data_kendalas jadi 1 tabel import_logs generik
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipe', [
                'penduduk', 'keluarga', 'objek_lapangan',
                'dataset_value', 'kendala', 'lainnya',
            ])->notNull();
            $table->string('sumber', 64)->nullable()->comment('excel, csv, api, mobile, manual');
            $table->string('file_name')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->json('payload_sample')->nullable();
            $table->enum('status', ['processing', 'completed', 'failed', 'partial'])->default('processing');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['tipe', 'status'], 'import_logs_tipe_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
