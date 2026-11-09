<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobile_field_points', function (Blueprint $table): void {
            $table->id();
            $table->uuid('operation_id')->unique();
            $table->foreignId('desa_id')->constrained('wilayahs')->restrictOnDelete();
            $table->string('jenis', 40)->index();
            $table->string('kode', 120)->nullable();
            $table->string('nama', 255);
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('attributes')->nullable();
            $table->json('keluarga_dataset_value_ids')->nullable();
            $table->string('status', 32)->default('submitted');
            $table->foreignId('submission_batch_id')->nullable()->constrained('submission_batches')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['desa_id', 'jenis', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_field_points');
    }
};
