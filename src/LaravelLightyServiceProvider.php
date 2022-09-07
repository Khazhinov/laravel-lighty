<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty;

use Illuminate\Support\ServiceProvider;
use Khazhinov\LaravelLighty\Console\Commands\Generator;
use Khazhinov\LaravelLighty\Console\Commands\Generator\ControllerGenerator;
use Khazhinov\LaravelLighty\Console\Commands\Generator\ModelGenerator;
use Khazhinov\LaravelLighty\Console\Commands\Generator\RequestGenerator;
use Khazhinov\LaravelLighty\Console\Commands\Generator\ResourceGenerator;
use Khazhinov\LaravelLighty\Console\Commands\Generator\RouteGenerator;

class LaravelLightyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishables();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'common-server');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/common-system.php', 'common-system');
        $this->registerCommands();
    }

    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/common-system.php' => config_path('common-system.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/common-server'),
        ], 'views');
    }

    protected function registerCommands(): void
    {
        $this->app->bind('command.service:generate-controller', ControllerGenerator::class);
        $this->app->bind('command.service:generate-model', ModelGenerator::class);
        $this->app->bind('command.service:generate-request', RequestGenerator::class);
        $this->app->bind('command.service:generate-resource', ResourceGenerator::class);
        $this->app->bind('command.service:generate-route', RouteGenerator::class);
        $this->app->bind('command.service:generator', Generator::class);

        $this->commands([
            'command.service:generate-controller',
            'command.service:generate-model',
            'command.service:generate-request',
            'command.service:generate-resource',
            'command.service:generate-route',
            'command.service:generator',
        ]);
    }
}
