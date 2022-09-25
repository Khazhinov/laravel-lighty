<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SuccessCollectionResourceResponse
{
    public static function build(
        SchemaContract $item,
        string $response_description = 'Успешный ответ',
        bool $is_pagination_enable = true,
        int $code = 200,
        string $message = 'OK',
    ): Response {
        $properties = [];
        if ($is_pagination_enable) {
            $properties[] = Schema::object('meta')->properties(
                Schema::integer('current_page')->default(1)->description('Текущая страница'),
                Schema::integer('from')->default(1)->description('Минимальный порядковый номер элемента коллекции на странице'),
                Schema::integer('last_page')->default(1)->description('Последняя страница'),
                Schema::array('links')->items(
                    Schema::object()->properties(
                        Schema::string('url')->default('http://route?page=1')->nullable()->description('Ссылка на страницу'),
                        Schema::string('label')->default('&laquo; Previous')->description('Метка страницы'),
                        Schema::boolean('active')->default(true)->description('Доступность страницы'),
                    )
                )->description('Список ссылок на страницы'),
                Schema::integer('per_page')->default(10)->description('Количество элементов на странице'),
                Schema::integer('to')->default(10)->description('Максимальный порядковый номер элемента коллекции на странице'),
                Schema::integer('total')->default(10)->description('Общее количество элементов коллекции'),
            );
        }

        return SuccessResponse::build(
            data: $item,
            additional_properties: $properties,
            response_description: $response_description,
            data_type: 'array',
            code: $code,
            message: $message
        );
    }
}
