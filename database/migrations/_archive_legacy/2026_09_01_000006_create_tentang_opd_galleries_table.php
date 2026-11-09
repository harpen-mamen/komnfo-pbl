<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tentang_opd_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tentang_opd_id')->constrained('tentang_opd')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('caption', 255)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentang_opd_galleries');
    }
};
