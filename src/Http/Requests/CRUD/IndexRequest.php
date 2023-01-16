<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Requests\CRUD;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

class IndexRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            ],
            'export.file_name' => [
                'sometimes',
                'string',
            ],
            'export.fields' => [
                'sometimes',
                'array',
                'min:1'
            ],
            'export.fields.*' => [
                'sometimes',
                'array',
            ],
            'export.fields.*.column' => [
                'required_with:export',
                'string',
                'min:1',
                'max:255',
            ],
            'export.fields.*.alias' => [
                'required_with:export',
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }
}
