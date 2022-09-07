<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Requests\CRUD;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterBooleanEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterOperatorEnum;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\Http\Requests\Enum;

class IndexRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'filter' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'filter.*' => [
                'sometimes',
                'array',
            ],
            'filter.*.column' => [
                'required_with:filter',
                'string',
                'min:1',
                'max:255',
            ],
            'filter.*.operator' => [
                'sometimes',
                'string',
                new Enum(type: IndexActionRequestPayloadFilterOperatorEnum::class),
            ],
            'filter.*.value' => [
                'nullable',
            ],
            'filter.*.boolean' => [
                'sometimes',
                'string',
                new Enum(type: IndexActionRequestPayloadFilterBooleanEnum::class),
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:300',
            ],
            'order' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'order.*' => [
                'required_with:order',
                'string',
            ],
            'with' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'with.relationships' => [
                'sometimes',
                'array',
            ],
            'with.relationships.*' => [
                'required_with:with.relationships',
                'string',
            ],
            'with.properties' => [
                'sometimes',
                'array',
            ],
            'with.properties.*' => [
                'required_with:with.properties',
                'string',
            ],
            'export' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'export.*' => [
                'sometimes',
                'array',
            ],
            'export.*.column' => [
                'required_with:export',
                'string',
                'min:1',
                'max:255',
            ],
            'export.*.alias' => [
                'required_with:export',
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }
}
