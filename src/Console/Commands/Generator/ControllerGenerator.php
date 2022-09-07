<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Console\Commands\Generator;

final class ControllerGenerator extends BaseGenerator
{
    /**
     * @var array<string, string>
     */
    private array $model_meta;

    /**
     * @var array<string, string>
     */
    private array $available_stubs = [
        'api' => 'controller.api.stub',
        'api-crud' => 'controller.api.crud.stub',
    ];

    private string $current_stub;

    public function initGeneratorParams(): void
    {
        $this->default_generator_namespace = 'App\Http\Controllers';
        $this->default_generator_dir = 'Http/Controllers';
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "lighty:generate-controller 
                            {controller_name : Название контроллера. Используйте слэш (/) для вложенности.} 
                            {model_name : Название модели. Используйте слэш (/) для вложенности.} 
                            {api_version : Версия разрабатываемого API, например V1_0.}
                            {--type=api-crud : Тип контроллера - a|api, ac|api-crud}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Метод для генерации контроллеров с использованием предлагаемой пакетом архитектуры.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->initGeneratorParams();

        if (! $this->checkType()) {
            return 1;
        }

        /** @var string $prefix */
        $prefix = $this->argument('api_version');
        $this->default_generator_dir .= '/Api/' . $prefix;
        $this->default_generator_namespace .= '\\Api\\' . $prefix;

        /** @var string $controller_name */
        $controller_name = $this->argument('controller_name');
        $this->initGenerator($controller_name);

        /** @var string $type */
        $type = $this->option('type');
        if ($this->isCrud($type)) {
            /** @var string $model_name */
            $model_name = $this->argument('model_name');
            $this->model_meta = $this->getMetaFromClassName(
                $model_name,
                'App\Models',
                'Models'
            );
        }

        if ($this->checkClassExist($this->class_path)) {
            $this->output->warning("Контроллер [{$this->class_name}] уже существует.");

            return 1;
        }

        if (! $this->createClass()) {
            $this->output->warning("Не получилось создать контроллер ({$this->class_path}).");

            return 1;
        }

        $this->output->info("Контроллер [{$this->class_name}] создан успешно.");

        return 0;
    }

    private function checkType(): bool
    {
        switch ($type = $this->option('type')) {
            case 'a':
            case 'api':
                $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . $this->available_stubs['api'];
                if (! file_exists($this->current_stub)) {
                    $this->output->warning('Заготовка для API не найдена.');

                    return false;
                }

                break;
            case 'ac':
            case 'api-crud':
                $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR . $this->available_stubs['api-crud'];
                if (! file_exists($this->current_stub)) {
                    $this->output->warning('Заготовка для CRUD API не найдена.');

                    return false;
                }

                break;
            default:
                /** @var string $type */
                $this->output->warning("Неизвестный тип контролера: $type");

                return false;
        }

        return true;
    }

    public function makeClassData(): string
    {
        /** @var string $data */
        $data = @file_get_contents($this->current_stub);

        $data = str_ireplace("{{ controller_namespace }}", $this->class_namespace, $data);
        $data = str_ireplace("{{ controller_name }}", $this->class_name, $data);

        /** @var string $type */
        $type = $this->option('type');
        if ($this->isCrud($type)) {
            $data = str_ireplace("{{ model_namespace }}", $this->model_meta['namespace'], $data);
            $data = str_ireplace("{{ model_name }}", $this->model_meta['name'], $data);
        }

        return $data;
    }

    /**
     * @param string $check
     * @return bool
     */
    private function isCrud(string $check): bool
    {
        $crud = ['ac', 'api-crud'];

        return in_array($check, $crud);
    }
}
