<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk binding device mobile ke user admin_lapangan.
 *
 * Alur aktivasi:
 *   1. Admin OPD daftarkan device admin_lapangan via web panel
 *   2. Mobile auto-login dengan device_id (TANPA password)
 *
 * Satu user boleh punya beberapa device (mis: 1 admin + 1 tablet backup).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('device_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id', 120)->unique()
                ->comment('UUID dari mobile (lihat di info app)');
            $table->string('device_label', 100)->nullable()
                ->comment('cth: "HP Pak Yusuf", "Tablet Desa X"');
            $table->string('platform', 20)->nullable()
                ->comment('android|ios');
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active'], 'device_assignments_user_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_assignments');
    }
};
