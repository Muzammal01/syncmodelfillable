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
        // Register the command here
        // $this->app->singleton(SyncModelFillable::class);
        $this->commands([SyncModelFillable::class]);
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/syncfillable.php' => config_path('syncfillable.php'),
        ], 'syncmodelfillable-config');
    }
}
