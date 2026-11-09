<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $changes = [];
        foreach (['users' => ['desa_id', 'kecamatan_id'], 'submission_batches' => ['kecamatan_id']] as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach (Schema::getForeignKeys($table) as $foreign) {
                $column = $foreign['columns'][0] ?? null;
                if (count($foreign['columns']) !== 1 || ! in_array($column, $columns, true) || ! in_array($foreign['foreign_table'], ['desas', 'kecamatans'], true)) {
                    continue;
                }
                $hasValues = DB::table($table)->whereNotNull($column)->exists();
                if ($hasValues && Schema::hasTable($foreign['foreign_table'])) {
                    throw new RuntimeException("Petakan {$table}.{$column} dari tabel wilayah lama ke wilayahs sebelum migrasi; ID tidak diubah otomatis.");
                }
                $validRegions = DB::table('wilayahs')->select('id')
                    ->whereIn('tipe', $column === 'desa_id' ? ['desa', 'kampung'] : ['kecamatan']);
                if (DB::table($table)->whereNotNull($column)->whereNotIn($column, $validRegions)->exists()) {
                    throw new RuntimeException("Relasi {$table}.{$column} tidak cocok dengan wilayahs. Koreksi pemetaan terlebih dahulu.");
                }
                $changes[] = [$table, $column];
            }
        }

        // Preflight every reference before any DDL: preserve all existing IDs.
        foreach ($changes as [$table, $column]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
                $blueprint->foreign($column)->references('id')->on('wilayahs')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // The legacy tables no longer exist. Do not restore dangling constraints.
    }
};
