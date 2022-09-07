<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Validation;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class ExistsInParents implements Validator
{
    /**
     * @param  string  $parent
     * @param  bool  $nullable
     */
    public function __construct(
        private readonly string $parent,
        private readonly bool $nullable = false,
    ) {
    }

    public function validate(mixed $value): ValidationResult
    {
        if ($this->nullable && is_null($value)) {
            return ValidationResult::valid();
        }

        if (! class_exists($this->parent)) {
            return ValidationResult::invalid("Class $this->parent not available");
        }

        if (! is_a($value, $this->parent, true)) {
            return ValidationResult::invalid("Class $value must be inherited from class $this->parent");
        }

        return ValidationResult::valid();
    }
}
