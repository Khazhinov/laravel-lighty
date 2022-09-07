<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Validation;

use Attribute;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class NotNull implements Validator
{
    public function validate(mixed $value): ValidationResult
    {
        if (! $value) {
            return ValidationResult::invalid('The field must not be empty.');
        }

        return ValidationResult::valid();
    }
}
