<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_section_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_content_id')->constrained('about_section_contents')->cascadeOnDelete();
            $table->string('judul_media', 200)->nullable();
            $table->string('file_path', 255);
            $table->enum('tipe', ['foto', 'dokumen', 'video', 'audio'])->default('foto');
            $table->text('caption')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_section_media');
    }
};
