<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProvisionPortalAdmin extends Command
{
    protected $signature = 'sipandu:provision-portal-admin';
    protected $description = 'Create the dedicated public portal content administrator';

    public function handle(): int
    {
        $permissions = ['portal_content.view', 'portal_content.create', 'portal_content.update', 'portal_content.delete', 'portal_content.publish'];
        foreach ($permissions as $name) Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'admin_portal', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $email = 'admin.portal@sipandu.local';
        $password = Str::random(24);
        $user = User::firstOrNew(['email' => $email]);
        if ($user->exists && ! $user->hasRole('admin_portal')) {
            $this->error('Email sudah dipakai akun dengan role lain; akun tidak diubah.');
            return self::FAILURE;
        }
        if (! $user->exists) {
            $user->name = 'Admin Portal Publik';
            $user->password = Hash::make($password);
            $user->email_verified_at = now();
            $user->save();
        } else {
            $password = '(akun sudah ada; kata sandi tidak direset)';
        }
        $user->syncRoles([$role]);

        $this->line('email=' . $email);
        $this->line('password=' . $password);
        $this->line('role=' . $user->getRoleNames()->implode(','));
        $this->line('panel_access=' . ($user->canAccessPanel(app(\Filament\Panel::class)) ? 'yes' : 'no'));
        $this->line('published_portal_content=' . \App\Models\PortalContent::query()->where('is_published', true)->count());

        return self::SUCCESS;
    }
}
