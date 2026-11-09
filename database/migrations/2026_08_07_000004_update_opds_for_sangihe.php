<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            // Add singkatan for OPD
            if (!Schema::hasColumn('opds', 'singkatan')) {
                $table->string('singkatan', 20)->nullable()->after('nama');
            }

            // Add is_verifikator flag
            if (!Schema::hasColumn('opds', 'is_verifikator')) {
                $table->boolean('is_verifikator')->default(false)->after('singkatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            if (Schema::hasColumn('opds', 'singkatan')) {
                $table->dropColumn('singkatan');
            }
            if (Schema::hasColumn('opds', 'is_verifikator')) {
                $table->dropColumn('is_verifikator');
            }
        });
    }
};
