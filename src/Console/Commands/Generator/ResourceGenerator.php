<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Console\Commands\Generator;

final class ResourceGenerator extends BaseGenerator
{
    /**
     * @var array<string, string>
     */
    private array $model_meta;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "service:generate-resource 
                            {resource_name : Название ресурса. Используйте слэш (/) для вложенности.}
                            {model_name : Название модели. Используйте слэш (/) для вложенности.} 
                            {--type=single : Тип ресурса  - s|single or c|collection}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Метод для генерации ресурсов с использованием предлагаемой пакетом архитектуры.';

    /**
     * @var array<string, string>
     */
    private array $available_stubs = [
        'single' => 'resource.single.stub',
        'collection' => 'resource.collection.stub',
    ];

    private string $current_stub;

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

        /** @var string $resource_name */
        $resource_name = $this->argument('resource_name');
        $resource_meta = $this->getMetaFromClassName($resource_name);
        $this->default_generator_namespace .= '\\' . $resource_meta['name'];
        $this->default_generator_dir .= '/' . $resource_meta['name'];

        if ($this->isCollection()) {
            $resource_name .= 'Collection';
        } else {
            $resource_name .= 'Resource';
        }

        $this->initGenerator($resource_name);

        /** @var string $model_name */
        $model_name = $this->argument('model_name');
        $this->model_meta = $this->getMetaFromClassName(
            $model_name,
            'App\Models',
            'Models'
        );

        if ($this->checkClassExist($this->class_path)) {
            $this->output->warning("Ресурс [{$this->class_name}] уже существует.");

            return 1;
        }

        if (! $this->createClass()) {
            $this->output->warning("Не получилось создать ресурс ({$this->class_path}).");

            return 1;
        }

        $this->output->info("Ресурс [{$this->class_name}] создан успешно.");

        return 0;
    }

    /**
     * @return bool
     */
    private function checkType(): bool
    {
        switch ($type = $this->option('type')) {
            case 's':
            case 'single':
                $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR .  $this->available_stubs['single'];
                if (! file_exists($this->current_stub)) {
                    $this->output->warning('Заготовка для единичного ресурса не найдена.');

                    return false;
                }

                return true;
            case 'c':
            case 'collection':
                $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR .  $this->available_stubs['collection'];
                if (! file_exists($this->current_stub)) {
                    $this->output->warning('Заготовка для коллекции не найдена.');

                    return false;
                }

                return true;
            default:
                /** @var string $type */
                $this->output->warning('Неизвестный тип ресурса: ' . $type);
        }

        return false;
    }

    /**
     * @return string
     */
    public function makeClassData(): string
    {
        /** @var string $data */
        $data = @file_get_contents($this->current_stub);

        $data = str_ireplace("{{ resource_namespace }}", $this->class_namespace, $data);
        $data = str_ireplace("{{ resource_name }}", $this->class_name, $data);
        $data = str_ireplace("{{ model_namespace }}", $this->model_meta['namespace'], $data);
        $data = str_ireplace("{{ model_name }}", $this->model_meta['name'], $data);

        return $data;
    }

    /**
     * @return bool
     */
    private function isCollection(): bool
    {
        /** @var string $type */
        $type = $this->option('type');
        $collection = ['c', 'collection'];

        return in_array($type, $collection);
    }

    public function initGeneratorParams(): void
    {
        $this->default_generator_namespace = 'App\Http\Resources';
        $this->default_generator_dir = 'Http/Resources';
    }
}
