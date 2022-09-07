<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Custer;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use ReflectionException;
use RuntimeException;
use Spatie\DataTransferObject\Caster;

class DataTransferObjectCaster implements Caster
{
    /**
     * @param  array<mixed>  $types
     * @param  string  $dto_class
     */
    public function __construct(
        private array $types,
        private readonly string $dto_class,
    ) {
    }

    /**
     * @param  mixed  $value
     *
     * @return DataTransferObject
     * @throws ReflectionException
     */
    public function cast(mixed $value): DataTransferObject
    {
        if (! $this->types) {
            throw new RuntimeException("Empty types.");
        }

        if (! class_exists($this->dto_class)) {
            throw new RuntimeException("Class $this->dto_class not available");
        }

        if (! is_a($this->dto_class, DataTransferObject::class, true)) {
            throw new RuntimeException("Class $this->dto_class must be inherited from class DataTransferObject");
        }

        if ($value instanceof $this->dto_class) {
            return $value;
        }

        if (! $value) {
            return new $this->dto_class();
        }

        return new $this->dto_class(
            ...$value
        );
    }
}
