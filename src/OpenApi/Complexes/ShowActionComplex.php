<?php

namespace Khazhinov\LaravelLighty\OpenApi\Complexes;

use JsonException;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\ModelReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessSingleResourceResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\ShowAction\ShowActionArgumentsDTO;

class ShowActionComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws JsonException
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new ShowActionArgumentsDTO($arguments);
        $model_reflector = new ModelReflector();
        $complex_result = new ComplexFactoryResult();

        $complex_result->responses = [
            SuccessSingleResourceResponse::build(
                properties: $model_reflector->getSchemaForSingle($arguments->model_class, $arguments->single_resource),
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
