<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('audit_trails')) {
            Schema::create('audit_trails', function (Blueprint $table) {
                $table->id(); $table->string('entity_type'); $table->unsignedBigInteger('entity_id');
                $table->string('field',150); $table->longText('nilai_lama')->nullable(); $table->longText('nilai_baru')->nullable();
                $table->string('aksi',50); $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('pengguna')->nullable(); $table->string('role',100)->nullable();
                $table->string('ip',45)->nullable(); $table->text('browser')->nullable();
                $table->timestamp('created_at')->useCurrent(); $table->index(['entity_type','entity_id']);
            });
        }
        if (! Schema::hasTable('user_registrations')) {
            Schema::create('user_registrations', function (Blueprint $table) {
                $table->id(); $table->string('name'); $table->string('email')->unique(); $table->string('password');
                $table->string('nip',50)->nullable(); $table->string('no_hp',20)->nullable(); $table->string('jabatan',100)->nullable();
                $table->foreignId('kecamatan_id')->nullable()->constrained('wilayahs')->nullOnDelete();
                $table->foreignId('desa_id')->nullable()->constrained('wilayahs')->nullOnDelete();
                $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
                $table->string('requested_role',50); $table->string('status',32)->default('pending');
                $table->text('admin_notes')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable(); $table->timestamp('approved_at')->nullable(); $table->timestamps();
                $table->index(['status','created_at']); $table->index('requested_role');
            });
        }
    }

    public function down(): void
    {
        // Retain audit records and registration requests during rollback.
    }
};
