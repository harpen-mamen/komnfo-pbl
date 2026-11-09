<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('public_hero_settings', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->text('deskripsi')->nullable();
            $t->string('gambar')->nullable();
            $t->boolean('aktif')->default(true);
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('public_news', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->string('slug')->unique();
            $t->text('ringkasan')->nullable();
            $t->longText('isi')->nullable();
            $t->string('gambar')->nullable();
            $t->string('kategori')->default('Informasi');
            $t->boolean('terbit')->default(false);
            $t->timestamp('terbit_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    } public function down(): void
    {
        Schema::dropIfExists('public_news');
        Schema::dropIfExists('public_hero_settings');
    }
};
