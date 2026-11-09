<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('rumah', function (Blueprint $table): void {
            $table->text('alamat_lengkap')->nullable()->change();
        });

        Schema::create('rumah_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rumah_id')->constrained('rumah')->cascadeOnDelete();
            $table->foreignId('keluarga_id')->unique()->constrained('keluarga')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rumah_keluarga');

        DB::table('rumah')->whereNull('alamat_lengkap')->update(['alamat_lengkap' => '']);
        Schema::table('rumah', function (Blueprint $table): void {
            $table->text('alamat_lengkap')->nullable(false)->change();
        });
    }
};
