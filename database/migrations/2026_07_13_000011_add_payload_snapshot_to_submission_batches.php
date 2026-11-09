<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->json('payload_snapshot')->nullable()->after('notes');
            $table->unsignedInteger('version')->default(1)->after('payload_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropColumn(['payload_snapshot', 'version']);
        });
    }
};
