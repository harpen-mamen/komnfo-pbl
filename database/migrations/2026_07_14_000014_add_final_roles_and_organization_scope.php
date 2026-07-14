<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('opds')) {
            Schema::create('opds', function (Blueprint $table): void {
                $table->id();
                $table->string('kode', 32)->unique();
                $table->string('nama');
                $table->text('deskripsi')->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();

                $table->index(['aktif', 'nama'], 'opds_active_name_idx');
            });
        }

        if (! Schema::hasTable('dusuns')) {
            Schema::create('dusuns', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('desa_id')->constrained('desas')->restrictOnDelete();
                $table->string('kode', 64)->nullable();
                $table->string('nama');
                $table->timestamps();

                $table->unique(['desa_id', 'nama'], 'dusuns_desa_name_unique');
                $table->unique(['desa_id', 'kode'], 'dusuns_desa_code_unique');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->after('kecamatan_id')->constrained('desas')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->after('desa_id')->constrained('opds')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'opd_id')) {
                $table->dropConstrainedForeignId('opd_id');
            }

            if (Schema::hasColumn('users', 'desa_id')) {
                $table->dropConstrainedForeignId('desa_id');
            }
        });

        Schema::dropIfExists('dusuns');
        Schema::dropIfExists('opds');
    }
};
