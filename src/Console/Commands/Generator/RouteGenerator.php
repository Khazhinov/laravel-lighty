<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Console\Commands\Generator;

use Khazhinov\LaravelLighty\Console\BaseCommand;

final class RouteGenerator extends BaseCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "lighty:generate-route 
                            {model_name : Название модели. Используйте слэш (/) для вложенности.}
                            {--type=api : Тип роутера  - a|api}
                            {--api-version= : Версия API, например: v1.0}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Метод для генерации роутера с использованием предлагаемой пакетом архитектуры.';

    /**
     * @var array<string, string>
     */
    private array $available_stubs = [
        'api' => 'route.api.stub',
    ];

    private string $current_stub;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->checkType()) {
            return 1;
        }

        $data = $this->makeClassData();
        $this->output->writeln("<info>Сгенерированный роутер: </info>\n" . $data);

        return 0;
    }

    private function checkType(): bool
    {
        switch ($type = $this->option('type')) {
            case 'a':
            case 'api':
                $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR .  $this->available_stubs['api'];
                if (! file_exists($this->current_stub)) {
                    $this->output->warning('Заготовка ддя роутера вида API не найдена.');

                    return false;
                }

                return true;
            default:
                /** @var string $type */
                $this->output->warning('Неизвестный тип роутера: ' . $type);
        }

        return false;
    }

    public function makeClassData(): string
    {
        /** @var string $data */
        $data = @file_get_contents($this->current_stub);

        /** @var string $model_name */
        $model_name = $this->argument('model_name');

        /** @var string|null $api_version */
        $api_version = $this->option('api-version');

        if ($api_version) {
            $data = str_ireplace("{{ comment_route_path }}", $api_version . '/' . helper_string_plural(lcfirst($model_name)), $data);
            $data = str_ireplace("{{ route_path }}", helper_string_plural(lcfirst($model_name)), $data);
        } else {
            $data = str_ireplace("{{ comment_route_path }}", helper_string_plural(lcfirst($model_name)), $data);
            $data = str_ireplace("{{ route_path }}", helper_string_plural(lcfirst($model_name)), $data);
        }

        $data = str_ireplace("{{ route_path_snake }}", helper_string_snake(helper_string_plural($model_name)), $data);
        $data = str_ireplace("{{ model_class }}", $model_name, $data);
        $data = str_ireplace("{{ controller_name }}", "{$model_name}CRUDController", $data);

        return $data;
    }
}
