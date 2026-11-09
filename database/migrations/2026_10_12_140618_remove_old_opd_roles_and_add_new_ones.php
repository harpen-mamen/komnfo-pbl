<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menghapus role lama (puskes, sekolah, generic)
     * dan menambahkan role baru (berita, kepala dinas, bupati, wakil bupati)
     */
    public function up(): void
    {
        // Hapus role lama yang tidak diperlukan lagi
        $rolesToDelete = [
            'admin_kesehatan',
            'operator_kesehatan',
            'admin_pendidikan',
            'operator_pendidikan',
            'admin_pu',
            'operator_pu',
            'admin_sosial',
            'operator_sosial',
            'admin_ekonomi',
            'operator_ekonomi',
            'admin_kependudukan',
            'operator_kependudukan',
            'admin_lingkungan',
            'operator_lingkungan',
            'admin_puskes',
            'operator_puskes',
            'admin_sekolah',
            'operator_sekolah',
        ];

        foreach ($rolesToDelete as $roleName) {
            // Hapus dari role_user pivot
            DB::table('model_has_roles')
                ->where('role_id', function ($query) use ($roleName) {
                    $query->select('id')
                        ->from('roles')
                        ->where('name', $roleName)
                        ->limit(1);
                })
                ->delete();

            // Hapus role
            DB::table('roles')
                ->where('name', $roleName)
                ->delete();
        }

        // Hapus permission yang tidak diperlukan lagi
        $permissionsToDelete = [
            'puskes.view', 'puskes.create', 'puskes.update', 'puskes.delete',
            'sekolah.view', 'sekolah.create', 'sekolah.update', 'sekolah.delete',
        ];

        foreach ($permissionsToDelete as $permName) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', function ($query) use ($permName) {
                    $query->select('id')
                        ->from('permissions')
                        ->where('name', $permName);
                })
                ->delete();

            DB::table('permissions')
                ->where('name', $permName)
                ->delete();
        }

        // Tambahkan module berita jika belum ada
        $beritaPermissions = ['berita.view', 'berita.create', 'berita.update', 'berita.delete', 'berita.publish'];
        foreach ($beritaPermissions as $permName) {
            $exists = DB::table('permissions')->where('name', $permName)->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Clear cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore role lama (ini hanya dokumentasi, role lama sudah dihapus permanen)

        // Clear cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
