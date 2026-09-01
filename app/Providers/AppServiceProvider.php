<?php

namespace App\Providers;

use App\Filament\Pages\EditProfile;
use App\Filament\Pages\StatistikKesehatanOtomatis;
use App\Models\SubmissionBatch;
use App\Observers\SubmissionBatchObserver;
use App\Services\Authorization\WewenangService;
use App\Services\DataInput\DataInputService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // SPRINT 6: Register singleton services
        $this->app->singleton(WewenangService::class, function ($app) {
            return new WewenangService();
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(DataInputService::class, function ($app) {
            return new DataInputService(
                $app->make(WewenangService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom Filament pages
        Filament::registerPages([
            EditProfile::class,
            StatistikKesehatanOtomatis::class,
        ]);

        // Load the public stylesheet directly. Filament v2 treats a numeric
        // array entry as a named asset (for example, `0.css`), so using a
        // complete link ensures the browser receives the current theme.
        Filament::registerStyles([
            '<link rel="stylesheet" href="/css/filament-custom.css?v=20260811b" />',
        ]);

        // Observe SubmissionBatch → sync status to MobileSyncOperation
        SubmissionBatch::observe(SubmissionBatchObserver::class);
    }
}
