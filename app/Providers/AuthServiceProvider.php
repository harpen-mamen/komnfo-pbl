<?php

namespace App\Providers;

use App\Models\Keluarga;
use App\Models\ImportPendudukLog;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Policies\ImportPendudukLogPolicy;
use App\Policies\KeluargaPolicy;
use App\Policies\PendudukPolicy;
use App\Policies\RumahPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Rumah::class => RumahPolicy::class,
        Keluarga::class => KeluargaPolicy::class,
        Penduduk::class => PendudukPolicy::class,
        ImportPendudukLog::class => ImportPendudukLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
