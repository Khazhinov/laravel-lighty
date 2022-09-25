<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;

class SuccessSingleResourceResponse
{
    /**
     * @param  SchemaContract[]  $properties
     * @param  string  $response_description
     * @param  int  $code
     * @param  string  $message
     * @return Response
     */
    public static function build(
        array $properties,
        string $response_description = 'Успешный ответ',
        int $code = 200,
        string $message = 'OK',
    ): Response {
        return SuccessResponse::build(
            data: $properties,
            response_description: $response_description,
            data_type: 'object',
            code: $code,
            message: $message
        );
    }
}
