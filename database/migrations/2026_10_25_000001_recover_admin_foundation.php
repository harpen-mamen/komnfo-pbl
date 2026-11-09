<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Additive recovery for the interrupted legacy migration chain. Existing
// accounts, roles and tables are preserved; regional keys use wilayahs.
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('opds')) {
            Schema::create('opds', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 32)->unique();
                $table->string('nama');
                $table->string('singkatan', 100)->nullable();
                $table->text('deskripsi')->nullable();
                $table->boolean('aktif')->default(true);
                $table->boolean('is_verifikator')->default(false);
                $table->timestamps();
                $table->index(['aktif', 'nama']);
            });
        }
        Schema::table('users', function (Blueprint $table) {
            foreach (['kecamatan_id', 'desa_id'] as $column) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->foreignId($column)->nullable()->constrained('wilayahs')->nullOnDelete();
                }
            }
            if (! Schema::hasColumn('users', 'opd_id')) {
                $table->foreignId('opd_id')->nullable()->constrained('opds')->nullOnDelete();
            }
        });
        if (! Schema::hasTable('periode_pelaporans')) {
            Schema::create('periode_pelaporans', function (Blueprint $table) {
                $table->id();
                $table->string('nama_periode');
                $table->unsignedTinyInteger('bulan');
                $table->unsignedSmallInteger('tahun');
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->string('status')->default('Draft');
                $table->timestamps();
                $table->index(['status', 'tahun', 'bulan']);
            });
        }
        if (! Schema::hasTable('submission_batches')) {
            Schema::create('submission_batches', function (Blueprint $table) {
                $table->id();
                $table->string('submission_number')->unique();
                $table->foreignId('kecamatan_id')->nullable()->constrained('wilayahs')->nullOnDelete();
                $table->string('semester', 32)->nullable();
                $table->unsignedSmallInteger('tahun')->nullable();
                $table->string('status', 32)->default('draft')->index();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        if (! Schema::hasTable('submission_batch_items')) {
            Schema::create('submission_batch_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_batch_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->timestamps();
                $table->unique(['submission_batch_id', 'entity_type', 'entity_id'], 'submission_batch_items_unique');
            });
        }
    }

    public function down(): void
    {
        // Recovery preserves existing data; rollback requires restoring its backup.
    }
};
