<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use TypeError;

class Enum implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (is_null($value) || ! function_exists('enum_exists') || ! enum_exists($this->type) || ! method_exists(
            $this->type,
            'tryFrom'
        )) {
            return false;
        }

        try {
            return ! is_null($this->type::tryFrom($value));
        } catch (TypeError $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return array<int|string, string>
     */
    public function message(): array
    {
        /** @var array<int|string, string>|string $message */
        $message = trans('validation.enum');

        if (is_string($message)) {
            return ['The selected :attribute is invalid.'];
        }

        return $message;
    }
}
