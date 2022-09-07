<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Custer;

use LogicException;
use RuntimeException;
use Spatie\DataTransferObject\Caster;

class EnumCaster implements Caster
{
    /**
     * @param  array<mixed>  $types
     * @param  string  $enumType
     */
    public function __construct(
        private readonly array $types,
        private readonly string $enumType
    ) {
    }

    public function cast(mixed $value): mixed
    {
        if (! $this->types) {
            throw new RuntimeException("Empty types.");
        }

        if (! is_subclass_of($this->enumType, 'BackedEnum')) {
            throw new LogicException("Caster [EnumCaster] may only be used to cast backed enums. Received [$this->enumType].");
        }

        if (is_subclass_of($value, 'BackedEnum') && ($value instanceof $this->enumType)) {
            $castedValue = $value;
        } else {
            $castedValue = $this->enumType::tryFrom($value);

            if ($castedValue === null) {
                throw new LogicException("Couldn't cast enum [$this->enumType] with value [$value]");
            }
        }

        return $castedValue;
    }
}
