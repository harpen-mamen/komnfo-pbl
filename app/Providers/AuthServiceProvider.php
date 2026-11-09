<?php

namespace App\Providers;

use App\Models\DatasetValue;
use App\Models\Keluarga;
use App\Models\ImportPendudukLog;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Policies\DatasetValuePolicy;
use App\Policies\ImportPendudukLogPolicy;
use App\Policies\KeluargaPolicy;
use App\Policies\PendudukPolicy;
use App\Policies\RumahPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Support\PanelAccess;

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
        DatasetValue::class => DatasetValuePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define(
            'export-identitas',
            fn (): bool =>
            PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner()
        );
    }
}
