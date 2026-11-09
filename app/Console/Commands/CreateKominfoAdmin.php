<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateKominfoAdmin extends Command
{
    protected $signature = 'app:create-kominfo-admin {--email=kominfo@sipdt.test} {--reset-password : Reset password for an existing Kominfo account}';

    protected $description = 'Create and verify a Kominfo administrator with a randomly generated password.';

    public function handle(): int
    {
        foreach (['users', 'roles', 'model_has_roles'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Required table missing: {$table}");
                return self::FAILURE;
            }
        }

        $email = (string) $this->option('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');
            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();
        if ($existing && ! $this->option('reset-password')) {
            $this->error('Account already exists. Use --reset-password to explicitly reset its password.');
            return self::FAILURE;
        }
        if ($existing && ! $existing->hasRole('admin_kominfo')) {
            $this->error('Existing account is not a Kominfo administrator; no changes made.');
            return self::FAILURE;
        }

        $password = Str::password(20);
        $user = DB::transaction(function () use ($existing, $email, $password): User {
            $role = Role::findOrCreate('admin_kominfo', 'web');
            $user = $existing ?? new User(['name' => 'Administrator Kominfo', 'email' => $email]);
            $user->password = Hash::make($password);
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole($role);
            return $user;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user = $user->fresh();
        if (! Auth::guard('web')->validate(['email' => $email, 'password' => $password]) || ! $user->canAccessFilament()) {
            $this->error('Account created but authentication verification failed.');
            return self::FAILURE;
        }

        $this->info('Authentication and Filament role verified.');
        $this->line('Database: '.DB::connection()->getDatabaseName());
        $this->line('Email: '.$email);
        $this->line('Password: '.$password);
        $this->line('Role: admin_kominfo');
        foreach (['opds', 'datasets', 'dataset_values', 'wilayahs'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->warn("Dashboard dependency missing: {$table}");
            }
        }

        return self::SUCCESS;
    }
}
