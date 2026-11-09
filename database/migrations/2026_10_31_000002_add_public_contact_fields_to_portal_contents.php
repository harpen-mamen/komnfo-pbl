<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_contents', function (Blueprint $table): void {
            if (! Schema::hasColumn('portal_contents', 'abbreviation')) $table->string('abbreviation', 80)->nullable();
            if (! Schema::hasColumn('portal_contents', 'address')) $table->string('address', 500)->nullable();
            if (! Schema::hasColumn('portal_contents', 'phone')) $table->string('phone', 80)->nullable();
            if (! Schema::hasColumn('portal_contents', 'contact_email')) $table->string('contact_email', 255)->nullable();
            if (! Schema::hasColumn('portal_contents', 'website')) $table->string('website', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('portal_contents', function (Blueprint $table): void {
            $columns = array_filter(['abbreviation', 'address', 'phone', 'contact_email', 'website'], fn (string $column): bool => Schema::hasColumn('portal_contents', $column));
            if ($columns !== []) $table->dropColumn($columns);
        });
    }
};
