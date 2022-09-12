<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Console\Commands;

use Khazhinov\LaravelLighty\Console\BaseCommand;

final class Generator extends BaseCommand
{
    protected $signature = "lighty:generator 
                            {model_name : Наименование модели.}
                            {api_version : Версия разрабатываемого API, например V1_0.}
                            {--migration : Флаг, говорящий о необходимости генерации миграций.}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Метод для комплексной генерации базовых сущностей с использованием предлагаемой пакетом архитектуры.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var string $model_name */
        $model_name = $this->argument('model_name');
        /** @var string $model_name */
        $api_version = $this->argument('api_version');

        $this->call('lighty:generate-model', [
            'model_name' => $model_name,
        ]);
        $this->call('lighty:generate-resource', [
            'resource_name' => $model_name,
            'model_name' => $model_name,
        ]);
        $this->call('lighty:generate-resource', [
            'resource_name' => $model_name,
            'model_name' => $model_name,
            '--type' => 'c',
        ]);
        $this->call('lighty:generate-controller', [
            'controller_name' => "{$model_name}/{$model_name}CRUDController",
            'model_name' => $model_name,
            'api_version' => $api_version,
            '--type' => 'ac',
        ]);
        $this->call('lighty:generate-request', [
            'request_name' => "{$model_name}/{$model_name}StoreRequest",
        ]);
        $this->call('lighty:generate-request', [
            'request_name' => "{$model_name}/{$model_name}UpdateRequest",
        ]);

        if ($this->option('migration')) {
            $this->call('make:migration', [
                'name' => "create_" . helper_string_plural(helper_string_snake($model_name)) . "_table",
            ]);
        }

        $this->call('lighty:generate-route', [
            'model_name' => $model_name,
            '--api-version' => $api_version,
        ]);

        return 0;
    }
}
