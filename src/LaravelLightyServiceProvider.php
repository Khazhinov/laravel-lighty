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

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lighty');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lighty.php', 'lighty');
        $this->registerCommands();
    }

    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/common-system.php' => config_path('lighty.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lighty'),
        ], 'views');
    }

    protected function registerCommands(): void
    {
        $this->app->bind('command.lighty:generate-controller', ControllerGenerator::class);
        $this->app->bind('command.lighty:generate-model', ModelGenerator::class);
        $this->app->bind('command.lighty:generate-request', RequestGenerator::class);
        $this->app->bind('command.lighty:generate-resource', ResourceGenerator::class);
        $this->app->bind('command.lighty:generate-route', RouteGenerator::class);
        $this->app->bind('command.lighty:generator', Generator::class);

        $this->commands([
            'command.lighty:generate-controller',
            'command.lighty:generate-model',
            'command.lighty:generate-request',
            'command.lighty:generate-resource',
            'command.lighty:generate-route',
            'command.lighty:generator',
        ]);
    }
}
