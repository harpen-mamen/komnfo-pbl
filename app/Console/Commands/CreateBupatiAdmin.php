<?php

namespace App\Console\Commands;

use App\Enums\AppRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CreateBupatiAdmin extends Command
{
    protected $signature = 'bupati:create-admin {email : Email akun Bupati} {name : Nama pemegang akun} {--password= : Kata sandi; jika kosong sistem membuat sandi acak sekali pakai}';

    protected $description = 'Buat akun Admin Bupati untuk memonitor Dashboard Eksekutif';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = trim((string) $this->argument('name'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
            $this->error('Nama dan alamat email yang valid wajib diisi.');
            return self::INVALID;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('Email tersebut sudah digunakan. Tidak ada akun yang diubah.');
            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: Str::random(32));
        if (strlen($password) < 12) {
            $this->error('Kata sandi harus sekurangnya 12 karakter.');
            return self::INVALID;
        }

        $role = Role::query()->firstOrCreate([
            'name' => AppRole::ADMIN_BUPATI->value,
            'guard_name' => 'web',
        ]);

        $user = DB::transaction(function () use ($name, $email, $password, $role): User {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'jabatan' => 'Admin Bupati',
            ]);
            $user->assignRole($role);
            return $user;
        });

        $this->info('Akun Admin Bupati berhasil dibuat. Simpan kredensial ini; kata sandi tidak dapat ditampilkan kembali.');
        $this->table(['Akun', 'Nilai'], [
            ['Nama', $user->name],
            ['Email', $user->email],
            ['Role', AppRole::ADMIN_BUPATI->label()],
            ['Halaman monitoring', url('/admin/executive-dashboard')],
            ['Kata sandi', $password],
        ]);

        return self::SUCCESS;
    }
}
