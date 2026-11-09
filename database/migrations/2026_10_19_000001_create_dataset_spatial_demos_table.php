<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dataset_spatial_demos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('label', 120);
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('periode_label', 32)->nullable();
            $table->json('values');
            $table->timestamps();
            $table->unique(['dataset_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataset_spatial_demos');
    }
};
