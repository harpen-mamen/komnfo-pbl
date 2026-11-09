<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_contents') && ! Schema::hasColumn('portal_contents', 'video_path')) {
            Schema::table('portal_contents', function (Blueprint $table): void {
                $table->string('video_path')->nullable()->after('image_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('portal_contents') && Schema::hasColumn('portal_contents', 'video_path')) {
            Schema::table('portal_contents', function (Blueprint $table): void {
                $table->dropColumn('video_path');
            });
        }
    }
};
