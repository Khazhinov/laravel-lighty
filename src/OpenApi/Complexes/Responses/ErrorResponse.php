<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use RuntimeException;

class ErrorResponse
{
    public static function build(
        null|SchemaContract|string $error = null,
        string $response_description = 'Ответ с ошибкой',
        string $error_type = 'object',
        int $code = 400,
        string $message = 'Bad Request',
    ): Response {
        $available_error_types = ['object', 'array'];
        if (! in_array($error_type, $available_error_types)) {
            throw new RuntimeException(sprintf('Неверный тип ошибки (%s). Возможные типы: %s', $error_type, implode(',', $available_error_types)));
        }

        if (is_null($error)) {
            $error = Schema::string('example')
                ->default('error')
                ->description('Ошибка может быть представлена различными способами.')
            ;
        }

        $properties = [
            Schema::string('status')->default('error')->description('Статус запроса'),
            Schema::integer('code')->default($code)->description('Код запроса'),
            Schema::string('message')->default($message)->description('Сообщение запроса'),
        ];

        switch ($error_type) {
            case 'array':
                /** @var SchemaContract $error */
                $properties[] = Schema::array('error')->items(
                    $error
                );

                break;
            case 'object':
                /** @var SchemaContract $error */
                $properties[] = Schema::object('error')->properties(
                    $error
                );

                break;
            case 'string':
                /** @var string $error */
                $properties[] = Schema::string('error')->default(
                    $error
                );

                break;
            default:
                break;
        }

        return Response::badRequest()->description($response_description)->content(
            MediaType::json()->schema(
                Schema::object('')->properties(...$properties),
            )
        );
    }
}
