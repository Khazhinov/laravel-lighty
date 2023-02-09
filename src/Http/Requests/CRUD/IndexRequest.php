<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Requests\CRUD;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsExportExportTypeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option\IndexActionOptionsReturnTypeEnum;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

class IndexRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $return_type_enum_cases = IndexActionOptionsReturnTypeEnum::cases();
        $return_type_enum_values = [];
        foreach ($return_type_enum_cases as $return_enum_case) {
            $return_type_enum_values[] = sprintf('"%s"', $return_enum_case->value);
        }
        $export_type_enum_cases = IndexActionOptionsExportExportTypeEnum::cases();
        $export_type_enum_values = [];
        foreach ($export_type_enum_cases as $export_enum_case) {
            $export_type_enum_values[] = sprintf('"%s"', $export_enum_case->value);
        }

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
            'return_type' => [
                'sometimes',
                'string',
                sprintf('in:%s', implode(",", $return_type_enum_values)),
            ],
            'export' => [
                'sometimes',
                'array',
            ],
            'export.file_name' => [
                'sometimes',
                'string',
            ],
            'export.export_type' => [
                'sometimes',
                'string',
                sprintf('in:%s', implode(",", $export_type_enum_values)),
            ],
            'export.fields' => [
                'sometimes',
                'array',
                'min:1',
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
