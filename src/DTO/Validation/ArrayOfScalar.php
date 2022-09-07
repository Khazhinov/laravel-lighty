<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Validation;

use Attribute;
use Khazhinov\LaravelLighty\Enums\ScalarTypeEnum;
use Spatie\DataTransferObject\Validation\ValidationResult;
use Spatie\DataTransferObject\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class ArrayOfScalar implements Validator
{
    /**
     * @param ScalarTypeEnum $type
     */
    public function __construct(
        private readonly ScalarTypeEnum $type,
        private readonly bool $nullable = false,
    ) {
    }

    public function validate(mixed $value): ValidationResult
    {
        if ($this->nullable && is_null($value)) {
            return ValidationResult::valid();
        }

        if (! is_array($value)) {
            return ValidationResult::invalid("Field must be an array");
        }

        switch ($this->type) {
            case ScalarTypeEnum::Boolean:
                foreach ($value as $item) {
                    if (! is_bool($item)) {
                        return ValidationResult::invalid("Each array element must be of type Boolean");
                    }
                }

                break;
            case ScalarTypeEnum::Integer:
                foreach ($value as $item) {
                    if (! is_int($item)) {
                        return ValidationResult::invalid("Each array element must be of type Integer");
                    }
                }

                break;
            case ScalarTypeEnum::Float:
                foreach ($value as $item) {
                    if (! is_float($item)) {
                        return ValidationResult::invalid("Each array element must be of type Float");
                    }
                }

                break;
            case ScalarTypeEnum::String:
                foreach ($value as $item) {
                    if (! is_string($item)) {
                        return ValidationResult::invalid("Each array element must be of type String");
                    }
                }

                break;
        }

        return ValidationResult::valid();
    }
}
