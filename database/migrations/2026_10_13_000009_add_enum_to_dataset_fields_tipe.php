<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('dataset_fields', function (Blueprint $table): void {
            $table->enum('tipe', ['string', 'text', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'json', 'reference', 'enum'])
                ->default('string')->change();
        });
    }
    public function down(): void {}
};
