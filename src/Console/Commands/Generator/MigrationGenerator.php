<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Console\Commands\Generator;

use Khazhinov\LaravelLighty\Console\BaseCommand;

final class MigrationGenerator extends BaseCommand
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
    protected $signature = "lighty:generate-migration 
                            {table : Название таблицы.}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Метод для генерации миграции с использованием предлагаемой пакетом архитектуры.';

    /**
     * @var array<string, string>
     */
    private array $available_stubs = [
        'migration' => 'migration.api.stub',
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
        /** @var string $table */
        $table = $this->argument('table');
        $name = sprintf("create_%s_table", $table);
        $data = $this->makeClassData();
        @file_put_contents($this->getPath($name, database_path('migrations')), $data);
        $this->output->info(sprintf('Миграция [%s] создана успешно.', $name));

        return 0;
    }

    /**
     * @return string
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getPath(string $name, string $path): string
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    private function checkType(): bool
    {
        $this->current_stub = __DIR__ . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR .  $this->available_stubs['migration'];
        if (! file_exists($this->current_stub)) {
            $this->output->warning('Заготовка ддя миграции не найдена.');

            return false;
        }

        return true;
    }

    public function makeClassData(): string
    {
        /** @var string $data */
        $data = @file_get_contents($this->current_stub);

        /** @var string $model_name */
        $table = $this->argument('table');

        $data = str_ireplace("{{ table }}", $table, $data);

        return $data;
    }
}
