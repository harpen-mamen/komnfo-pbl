<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            // Add new columns for government structure
            $table->string('kategori', 50)->nullable()->after('deskripsi');
            $table->unsignedBigInteger('parent_id')->nullable()->after('kategori');
            $table->string('alamat', 500)->nullable()->after('parent_id');
            $table->string('telepon', 20)->nullable()->after('alamat');
            $table->string('email', 255)->nullable()->after('telepon');
            $table->string('kepala_dinas', 255)->nullable()->after('email');
            $table->string('nip_kepala_dinas', 50)->nullable()->after('kepala_dinas');

            // Add foreign key for parent OPD
            $table->foreign('parent_id')
                ->references('id')
                ->on('opds')
                ->onDelete('set null');

            // Add index for faster lookups
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('opds', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'kategori',
                'parent_id',
                'alamat',
                'telepon',
                'email',
                'kepala_dinas',
                'nip_kepala_dinas',
            ]);
        });
    }
};
