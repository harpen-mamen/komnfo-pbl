<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mobile_sync_operations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('operation_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id', 120);
            $table->string('dataset', 80);
            $table->string('local_id', 120);
            $table->unsignedInteger('base_version')->default(0);
            $table->json('payload');
            $table->string('status', 30)->default('accepted');
            $table->string('conflict_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'operation_id']);
            $table->index(['user_id', 'id']);
            $table->index(['dataset', 'local_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_sync_operations');
    }
};
