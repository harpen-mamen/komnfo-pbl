<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workflow_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('workflow_logs', 'data_value_id')) {
                $table->foreignId('data_value_id')->nullable()->constrained('dataset_values')->nullOnDelete();
            }
            if (! Schema::hasColumn('workflow_logs', 'workflow_stage')) {
                $table->string('workflow_stage', 32)->nullable();
            }
            if (! Schema::hasColumn('workflow_logs', 'reason')) {
                $table->text('reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflow_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('data_value_id');
            $table->dropColumn(['workflow_stage', 'reason']);
        });
    }
};
