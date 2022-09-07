<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Validation;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class InClassField implements Validator
{
    public function __construct(
        protected string $class,
        protected string $static_field,
        protected bool $nullable = true,
    ) {
    }

    public function validate(mixed $value): ValidationResult
    {
        if (is_null($value)) {
            if (! $this->nullable) {
                return ValidationResult::invalid("The field must not be empty");
            }

            return ValidationResult::valid();
        }

        $field = $this->static_field;

        if (! property_exists($this->class, $this->static_field)) {
            return ValidationResult::invalid("Property [$field] does not exists in class [$this->class].");
        }

        if (! is_array($this->class::$$field)) {
            return ValidationResult::invalid("Property [$field] of class [$this->class] must by type of array.");
        }

        if (! in_array($value, $this->class::$$field, true)) {
            return ValidationResult::invalid('The field must be in one of these values: '.implode(', ', $this->class::$$field));
        }

        return ValidationResult::valid();
    }
}
