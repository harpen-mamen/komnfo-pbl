<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'kecamatan_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('kecamatan_id')->nullable()->after('password')->constrained('kecamatans')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'desa_id')) {
            DB::table('users')
                ->join('desas', 'users.desa_id', '=', 'desas.id')
                ->whereNull('users.kecamatan_id')
                ->update(['users.kecamatan_id' => DB::raw('desas.kecamatan_id')]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('desa_id');
            });
        }

        if (Schema::hasTable('roles')) {
            $oldRole = DB::table('roles')->where('name', 'admin_desa')->first();
            $newRole = DB::table('roles')->where('name', 'admin_kecamatan')->first();

            if ($oldRole && ! $newRole) {
                DB::table('roles')->where('id', $oldRole->id)->update(['name' => 'admin_kecamatan']);
            } elseif ($oldRole && $newRole && Schema::hasTable('model_has_roles')) {
                DB::table('model_has_roles')->where('role_id', $oldRole->id)->update(['role_id' => $newRole->id]);
                DB::table('roles')->where('id', $oldRole->id)->delete();
            }
        }

        if (Schema::hasTable('import_gagal') && ! Schema::hasColumn('import_gagal', 'kecamatan_id')) {
            Schema::table('import_gagal', function (Blueprint $table) {
                $table->foreignId('kecamatan_id')->nullable()->after('desa_id')->constrained('kecamatans')->nullOnDelete();
                $table->index('kecamatan_id', 'import_gagal_kecamatan_id_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('import_gagal') && Schema::hasColumn('import_gagal', 'kecamatan_id')) {
            Schema::table('import_gagal', function (Blueprint $table) {
                $table->dropIndex('import_gagal_kecamatan_id_lookup_idx');
                $table->dropConstrainedForeignId('kecamatan_id');
            });
        }

        if (! Schema::hasColumn('users', 'desa_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('desa_id')->nullable()->after('password')->constrained('desas')->nullOnDelete();
            });
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'admin_kecamatan')->update(['name' => 'admin_desa']);
        }
    }
};
