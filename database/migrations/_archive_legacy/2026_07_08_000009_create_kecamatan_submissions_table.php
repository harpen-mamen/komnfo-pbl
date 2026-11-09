<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('kecamatan_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->integer('rumah_count')->default(0);
            $table->integer('keluarga_count')->default(0);
            $table->integer('penduduk_count')->default(0);
            $table->string('status', 32)->default('draft'); // draft, dikirim, diverifikasi, perlu_revisi, disetujui, ditolak
            $table->text('notes')->nullable();
            $table->text('kominfo_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('kecamatan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kecamatan_submissions');
    }
};
