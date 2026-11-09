<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('dataset_values', 'updated_by')) {
            Schema::table('dataset_values', function (Blueprint $table) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('dataset_values', fn (Blueprint $table) => $table->dropConstrainedForeignId('updated_by'));
    }
};
