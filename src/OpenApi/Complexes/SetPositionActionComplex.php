<?php

namespace Khazhinov\LaravelLighty\OpenApi\Complexes;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\SetPositionAction\SetPositionActionArgumentsDTO;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class SetPositionActionComplex extends ComplexFactory
{
    /**
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new SetPositionActionArgumentsDTO($arguments);
        $complex_result = new ComplexFactoryResult();

        $complex_result->request_body = RequestBody::create()->content(
            MediaType::json()->schema(
                Schema::object('')->properties(
                    Schema::array('ids')->items(
                        Schema::string()
                    )->description('Идентификаторы сущностей, которые требуется позиционировать'),
                )
            ),
        );

        $complex_result->responses = [
            SuccessResponse::build(
                data: [Schema::string('status')->default('ok')->description('Сообщение об успешном выполнении операции')]
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
