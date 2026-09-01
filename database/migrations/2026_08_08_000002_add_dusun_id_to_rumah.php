<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rumah', 'dusun_id')) {
            Schema::table('rumah', function (Blueprint $table): void {
                $table->foreignId('dusun_id')->nullable()->after('desa_id')->constrained('dusuns')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rumah', 'dusun_id')) {
            Schema::table('rumah', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('dusun_id');
            });
        }
    }
};
