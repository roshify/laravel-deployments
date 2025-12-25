<?php

namespace SthiraLabs\LaravelDeployments;

use Illuminate\Support\ServiceProvider;
use SthiraLabs\LaravelDeployments\Console\Commands\DeployRun;
use SthiraLabs\LaravelDeployments\Console\Commands\DeployStatus;
use SthiraLabs\LaravelDeployments\Console\Commands\DeployRollback;
use SthiraLabs\LaravelDeployments\Console\Commands\MakeDeployment;

class DeploymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/deployments.php', 'deployments');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DeployRun::class,
                DeployStatus::class,
                DeployRollback::class,
                MakeDeployment::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/deployments.php' => config_path('deployments.php'),
            ], 'deployments-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'deployments-migrations');

            $this->publishes([
                __DIR__.'/../stubs/deployment-script.stub' => base_path('stubs/deployment-script.stub'),
            ], 'deployments-stubs');
        }
    }
}