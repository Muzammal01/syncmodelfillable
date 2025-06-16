<?php

namespace Muzammal\Syncmodelfillable;

use Illuminate\Support\ServiceProvider;
use Muzammal\Syncmodelfillable\Console\SyncModelFillable;

class SyncModelFillableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->commands([SyncModelFillable::class]);

        // Merge the package's config file
        $this->mergeConfigFrom(__DIR__ . '/../config/syncfillable.php', 'syncfillable');
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__ . '/../config/syncfillable.php' => config_path('syncfillable.php'),
        ], 'syncmodelfillable-config');

        // Register the rollback command
        $this->commands([
            \Muzammal\Syncmodelfillable\Console\RollbackSyncModelFillable::class,
        ]);
    }
}