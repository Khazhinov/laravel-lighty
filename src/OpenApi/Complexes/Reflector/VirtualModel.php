<?php
declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector;

use Illuminate\Database\Eloquent\Model;

class VirtualModel
{
    public function __construct(
        protected Model $source_model,
        /** @var array<string, mixed> */
        protected array $attributes = [])
    {}

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->source_model, $name)) {
            return $this->source_model->$name(...$arguments);
        }

        return null;
    }

    public function __get(string $name)
    {
        return helper_array_get($this->attributes, $name);
    }
}
