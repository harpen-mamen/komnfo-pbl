<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mobile_sync_operations', function (Blueprint $table): void {
            if (! Schema::hasColumn('mobile_sync_operations', 'server_id')) {
                $table->unsignedBigInteger('server_id')->nullable()->after('status');
            }

            if (! Schema::hasColumn('mobile_sync_operations', 'error_message')) {
                $table->text('error_message')->nullable()->after('conflict_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_sync_operations', function (Blueprint $table): void {
            if (Schema::hasColumn('mobile_sync_operations', 'error_message')) {
                $table->dropColumn('error_message');
            }

            if (Schema::hasColumn('mobile_sync_operations', 'server_id')) {
                $table->dropColumn('server_id');
            }
        });
    }
};
