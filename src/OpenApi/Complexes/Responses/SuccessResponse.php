<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use RuntimeException;

class SuccessResponse
{
    /**
     * @param  SchemaContract|SchemaContract[]  $data
     * @param  SchemaContract[]  $additional_properties
     * @param  string  $response_description
     * @param  string  $data_type
     * @param  int  $code
     * @param  string  $message
     * @return Response
     */
    public static function build(
        SchemaContract|array|string $data,
        array $additional_properties = [],
        string $response_description = 'Успешный ответ',
        string $data_type = 'object',
        int $code = 200,
        string $message = 'OK',
    ): Response {
        $available_data_types = ['object', 'array', 'string'];
        if (! in_array($data_type, $available_data_types)) {
            throw new RuntimeException(sprintf('Неверный тип ошибки (%s). Возможные типы: %s', $data_type, implode(',', $available_data_types)));
        }

        $single_schema = Schema::object('');
        $properties = [
            Schema::string('status')->default('success')->description('Статус запроса'),
            Schema::integer('code')->default($code)->description('Код запроса'),
            Schema::string('message')->default($message)->description('Сообщение запроса'),
        ];

        switch ($data_type) {
            case 'array':
                /** @var SchemaContract $data */
                $properties[] = Schema::array('data')->items(
                    $data,
                );

                break;
            case 'object':
                /** @var SchemaContract[] $data */
                $properties[] = Schema::object('data')->properties(
                    ...$data,
                );

                break;
            case 'string':
                /** @var string $data */
                $properties[] = Schema::string('data')->default(
                    $data,
                );

                break;
            default:
                break;
        }

        foreach ($additional_properties as $additional_property) {
            $properties[] = $additional_property;
        }

        return Response::ok()->description($response_description)->content(
            MediaType::json()->schema(
                $single_schema->properties(...$properties)
            )
        );
    }
}
