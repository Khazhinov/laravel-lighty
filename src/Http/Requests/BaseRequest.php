<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @method array<string, mixed> rules()
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
//            'email' => 'email address',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        //        $this->merge([
        //            'slug' => Str::slug($this->slug),
        //        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
//            'title.required' => 'A title is required',
//            'body.required' => 'A message is required',
        ];
    }
}
