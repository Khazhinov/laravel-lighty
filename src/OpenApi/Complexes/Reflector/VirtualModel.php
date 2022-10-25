<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector;

use Illuminate\Database\Eloquent\Model;

class VirtualModel
{
    public function __construct(
        protected Model $source_model,
        /** @var array<string, mixed> */
        protected array $attributes = []
    ) {
    }

    /**
     * @param  string  $name
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->source_model, $name)) {
            return $this->source_model->$name(...$arguments);
        }

        return null;
    }

    public function __get(string $name): mixed
    {
        return helper_array_get($this->attributes, $name);
    }

    public function __set(string $name, mixed $value): void
    {
        helper_array_set($this->attributes, $name, $value);
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }
}
