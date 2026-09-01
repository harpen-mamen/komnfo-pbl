<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_section_contents', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 50)->unique();
            $table->string('judul', 200);
            $table->string('subjudul', 255)->nullable();
            $table->text('ringkasan')->nullable();
            $table->longText('konten')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('warna_aksen', 20)->nullable();
            $table->integer('urutan')->default(0);
            $table->enum('status_publikasi', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('tampil_di_menu')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status_publikasi', 'tampil_di_menu', 'urutan'], 'asc_status_tampil_urutan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_section_contents');
    }
};
