<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use TypeError;

class Enum implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  string  $type
     * @return void
     */
    public function __construct(
        public string $type
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var string $message */
        $message = trans('validation.enum');
        if (is_null($value) || ! function_exists('enum_exists') || ! enum_exists($this->type) || ! method_exists(
            $this->type,
            'tryFrom'
        )) {
            $fail($message)->translate();
        }

        try {
            if (is_null($this->type::tryFrom($value))) {
                $fail($message)->translate();
            };
        } catch (TypeError $e) {
            $fail($message)->translate();
        }
    }
}
