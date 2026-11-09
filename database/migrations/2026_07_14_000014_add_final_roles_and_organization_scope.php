<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
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

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'opd_id')) {
                $table->dropConstrainedForeignId('opd_id');
            }

        });

        Schema::dropIfExists('opds');
    }
};
