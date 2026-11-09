<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('workflow_status', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('warna_badge', 50)->nullable();
            $table->string('icon', 100)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        DB::table('workflow_status')->insert([
            ['kode' => 'draft', 'nama' => 'Draft', 'warna_badge' => 'gray', 'icon' => 'heroicon-o-document-text', 'urutan' => 1, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'menunggu_verifikasi', 'nama' => 'Menunggu Verifikasi', 'warna_badge' => 'yellow', 'icon' => 'heroicon-o-clock', 'urutan' => 2, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'sedang_diverifikasi', 'nama' => 'Sedang Diverifikasi', 'warna_badge' => 'blue', 'icon' => 'heroicon-o-search', 'urutan' => 3, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'perlu_revisi', 'nama' => 'Perlu Revisi', 'warna_badge' => 'orange', 'icon' => 'heroicon-o-pencil-alt', 'urutan' => 4, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'disetujui', 'nama' => 'Disetujui', 'warna_badge' => 'green', 'icon' => 'heroicon-o-check-circle', 'urutan' => 5, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'ditolak', 'nama' => 'Ditolak', 'warna_badge' => 'red', 'icon' => 'heroicon-o-x-circle', 'urutan' => 6, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'diarsipkan', 'nama' => 'Diarsipkan', 'warna_badge' => 'slate', 'icon' => 'heroicon-o-archive', 'urutan' => 7, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('status_id')->constrained('workflow_status')->restrictOnDelete();
            // Legacy regional tables are archived; current regional records live in `wilayahs`.
            // Keep these IDs nullable here so fresh installs do not create dangling legacy FKs.
            $table->foreignId('kecamatan_id')->nullable();
            $table->foreignId('desa_id')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_submit')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id'], 'workflow_instances_entity_unique');
            $table->index(['entity_type', 'entity_id'], 'workflow_instances_entity_lookup_idx');
            $table->index(['status_id', 'kecamatan_id', 'desa_id'], 'workflow_instances_scope_lookup_idx');
        });

        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('status_sebelumnya')->nullable()->constrained('workflow_status')->nullOnDelete();
            $table->foreignId('status_baru')->nullable()->constrained('workflow_status')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->text('alasan')->nullable();
            $table->foreignId('dilakukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['workflow_instance_id', 'created_at'], 'workflow_histories_timeline_idx');
        });

        Schema::create('verification_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('komentar');
            $table->string('tipe', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['workflow_instance_id', 'created_at'], 'verification_comments_timeline_idx');
        });

        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('field', 150);
            $table->longText('nilai_lama')->nullable();
            $table->longText('nilai_baru')->nullable();
            $table->string('aksi', 50);
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pengguna')->nullable();
            $table->string('role', 100)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('browser')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id'], 'audit_trails_entity_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
        Schema::dropIfExists('verification_comments');
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_status');
    }
};
