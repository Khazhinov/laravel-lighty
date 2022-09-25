<?php

namespace Khazhinov\LaravelLighty\OpenApi\Complexes;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use JsonException;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\ModelReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\RequestReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessSingleResourceResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\UpdateAction\UpdateActionArgumentsDTO;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class UpdateActionComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new UpdateActionArgumentsDTO($arguments);
        $model_reflector = new ModelReflector();
        $request_reflector = new RequestReflector();
        $complex_result = new ComplexFactoryResult();

        if ($arguments->validation_request) {
            $complex_result->request_body = RequestBody::create()->content(
                MediaType::json()->schema(
                    Schema::object('')->properties(
                        ...$request_reflector->getSchemaByRequest($arguments->validation_request)
                    )
                ),
            );
        }

        $complex_result->responses = [
            SuccessSingleResourceResponse::build(
                properties: $model_reflector->getSchemaForSingle($arguments->model_class, $arguments->single_resource),
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
