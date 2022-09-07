<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Console\Commands\Generator;

final class RequestGenerator extends BaseGenerator
{
    public function initGeneratorParams(): void
    {
        $this->default_generator_namespace = 'App\Http\Requests';
        $this->default_generator_dir = 'Http/Requests';
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "service:generate-request 
                            {request_name : Название запроса. Используйте слэш (/) для вложенности.}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Метод для генерации запросов с использованием предлагаемой пакетом архитектуры.';

    /**
     * @var array<string, string>
     */
    private array $available_stubs = [
        'base' => 'request.stub',
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

        if (! $this->initCurrentStub()) {
            return 1;
        }

        /** @var string $request_name */
        $request_name = $this->argument('request_name');
        $this->initGenerator($request_name);

        if ($this->checkClassExist($this->class_path)) {
            $this->output->warning("Запрос [{$this->class_name}] уже существует.");

            return 1;
        }

        if (! $this->createClass()) {
            $this->output->warning("Не получилось создать запрос ({$this->class_path}).");

            return 1;
        }

        $this->output->info("Запрос [{$this->class_name}] создан успешно.");

        return 0;
    }

    /**
     * @return bool
     */
    private function initCurrentStub(): bool
    {
        $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR .  $this->available_stubs['base'];
        if (! file_exists($this->current_stub)) {
            $this->output->warning('Заготовка с запросом не найдена.');

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function makeClassData(): string
    {
        /** @var string $data */
        $data = @file_get_contents($this->current_stub);

        $data = str_ireplace("{{ request_namespace }}", $this->class_namespace, $data);
        $data = str_ireplace("{{ request_name }}", $this->class_name, $data);

        return $data;
    }
}
