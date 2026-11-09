<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Wilayah;
use App\Models\SubmissionBatch;
use App\Observers\SubmissionBatchObserver;
use App\Services\Authorization\WewenangService;
use App\Services\DataInput\DataInputService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\ServeCommand;

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
        if ($this->app->runningInConsole()) {
            ServeCommand::$passthroughVariables = array_values(array_unique([
                ...ServeCommand::$passthroughVariables,
                'PHPRC', 'SIPANDU_PHP_DIR', 'SIPANDU_STORAGE_DIR', 'PHP_INI_SCAN_DIR',
            ]));
        }

        Relation::morphMap([
            'kabupaten' => Wilayah::class,
            'kecamatan' => Wilayah::class,
            'desa' => Wilayah::class,
        ]);

        // Observe SubmissionBatch → sync status to MobileSyncOperation
        SubmissionBatch::observe(SubmissionBatchObserver::class);
    }
}
