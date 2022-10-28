<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Console\Commands\Generator;

use Khazhinov\LaravelLighty\Console\BaseCommand;

abstract class BaseGenerator extends BaseCommand
{
    protected string $class_name;
    protected string $class_dir;
    protected string $class_namespace;
    protected string $class_path;
    protected string $class_dir_path;
    protected string $default_generator_namespace;
    protected string $default_generator_dir;

    abstract public function initGeneratorParams(): void;

    abstract public function makeClassData(): string;

    /**
     * @return bool
     */
    public function createClass(): bool
    {
        $data = $this->makeClassData();

        if (! $this->createDirectory($this->class_dir_path)) {
            return false;
        }

        $saved = @file_put_contents($this->class_path, $data, LOCK_EX);

        if ($saved === false) {
            return false;
        }

        return true;
    }

    /**
     * Первичная инициализация генератора
     *
     * @param string $name
     *
     * @return void
     */
    public function initGenerator(string $name): void
    {
        $meta = $this->getMetaFromClassName($name);

        $this->class_name = $meta['name'];
        $this->class_namespace = $meta['namespace'];
        $this->class_dir = $meta['dir'];
        $this->class_path = $meta['path'];
        $this->class_dir_path = $meta['dir_path'];
    }

    /**
     * Функция для получения полезной нагрузки из переданного класса
     *
     * @param  string  $name
     * @param  false|string  $default_generator_namespace
     * @param  false|string  $default_generator_dir
     *
     * @return array<string, string>
     */
    public function getMetaFromClassName(string $name, false|string $default_generator_namespace = false, false|string $default_generator_dir = false): array
    {
        $result = [
            'namespace' => $default_generator_namespace ?: $this->default_generator_namespace,
            'dir' => $default_generator_dir ?: $this->default_generator_dir,
        ];

        $argument_model_name = $name;
        $argument_model_dir = 'root';

        if (mb_stripos($argument_model_name, '/')) {
            $exploded_name = explode('/', $argument_model_name);
            $result['name'] = array_pop($exploded_name);
            $argument_model_dir = implode('/', $exploded_name);
        } else {
            $result['name'] = $argument_model_name;
        }

        if ($argument_model_dir !== 'root') {
            $result['dir'] .= '/' . $argument_model_dir;
            $result['namespace'] .= '\\' . str_ireplace('/', '\\', $argument_model_dir);
        }

        $result['path'] = app_path($result['dir'] . '/' . $result['name'] . '.php');
        $result['dir_path'] = app_path($result['dir']);

        return $result;
    }

    /**
     * Функция для проверки указанного пути
     *
     * @param string $path
     * @return bool
     */
    public function checkClassExist(string $path): bool
    {
        return @file_exists($path);
    }

    /**
     * Функция для генерации директорий
     *
     * @param  string  $directory
     * @return bool
     */
    public function createDirectory(string $directory): bool
    {
        return ! (! is_dir($directory) && ! @mkdir($directory, 0777, true) && ! is_dir($directory));
    }
}
