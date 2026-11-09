<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1: Objek Lapangan (Prinsip 7, 8, 19)
 *
 * Objek lapangan adalah data spasial yang dikumpulkan Admin Desa
 * via mobile. Contoh: Rumah, Sekolah, Kantor, Jalan, Jembatan, dll.
 *
 * Design:
 * - objek_lapangan_types: configurator (RUMAH, JALAN, JEMBATAN, dll)
 * - objek_lapangans: polymorphic root (1 record = 1 objek)
 * - attributes JSON: extensible per-type
 *
 * Geometry:
 * - POINT: Rumah, Sekolah, Kantor, Jembatan (single point)
 * - LINESTRING: Jalan, Sungai (path)
 * - POLYGON: Wilayah, Pasar (area)
 *
 * CATATAN: kolom `geometry` memerlukan MySQL spatial extension atau
 * PostGIS. Untuk saat ini lat/lng disimpan sebagai decimal. Kolom
 * geometry ditambahkan sebagai nullable untuk forward-compatibility.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('objek_lapangan_types', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 32)->unique()->comment('RUMAH, JALAN, JEMBATAN, dll');
            $table->string('nama', 255);
            $table->enum('geometry_type', ['point', 'linestring', 'polygon'])->default('point');
            $table->string('icon', 64)->nullable()->comment('heroicon name');
            $table->string('color', 32)->nullable()->comment('hex color for map display');
            $table->boolean('is_active')->default(true);
            $table->json('field_schema')->nullable()->comment('definisi field dinamis per tipe');
            $table->json('validation_rules')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'urutan'], 'objek_lapangan_types_active_idx');
        });

        Schema::create('objek_lapangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objek_lapangan_type_id')->constrained('objek_lapangan_types')->restrictOnDelete();
            $table->foreignId('desa_id')->constrained('desas')->restrictOnDelete();
            $table->foreignId('dusun_id')->nullable()->constrained('dusuns')->nullOnDelete();

            // Identitas
            $table->string('nama', 255)->nullable();
            $table->string('kode', 64)->nullable()->comment('kode referensi eksternal');
            $table->text('alamat')->nullable();

            // Geometry (POINT via lat/lng; LINESTRING/POLYGON via `geometry`)
            $table->decimal('koordinat_lat', 10, 7)->nullable();
            $table->decimal('koordinat_lng', 10, 7)->nullable();
            $table->text('geometry_wkt')->nullable()->comment('WKT format for LINESTRING/POLYGON, requires spatial extension');

            // Extensible attributes
            $table->json('attributes')->nullable();

            // Status verifikasi
            $table->enum('status_verifikasi', [
                'draft',
                'diajukan',
                'diverifikasi',
                'ditolak',
                'revisi',
            ])->default('draft');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable();

            // Source tracking
            $table->foreignId('source_attribution_id')->nullable()->constrained('source_attributions')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['objek_lapangan_type_id', 'status_verifikasi'], 'objek_lapangans_type_status_idx');
            $table->index(['desa_id'], 'objek_lapangans_desa_idx');
            $table->index(['koordinat_lat', 'koordinat_lng'], 'objek_lapangans_geo_idx');
            $table->index(['kode'], 'objek_lapangans_kode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objek_lapangans');
        Schema::dropIfExists('objek_lapangan_types');
    }
};
