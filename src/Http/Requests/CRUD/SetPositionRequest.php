<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Requests\CRUD;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

class SetPositionRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ];
    }
}
