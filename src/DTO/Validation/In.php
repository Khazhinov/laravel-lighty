<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Validation;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class In implements Validator
{
    /**
     * @param  array<mixed>  $values
     * @param  bool  $nullable
     */
    public function __construct(
        private readonly array $values,
        private readonly bool $nullable = true,
    ) {
    }

    public function validate(mixed $value): ValidationResult
    {
        if (is_null($value)) {
            if (! $this->nullable) {
                return ValidationResult::invalid("Field must not be empty");
            }

            return ValidationResult::valid();
        }

        if (! in_array($value, $this->values, true)) {
            return ValidationResult::invalid('Field must be in one of these values: '.implode(', ', $this->values));
        }

        return ValidationResult::valid();
    }
}
