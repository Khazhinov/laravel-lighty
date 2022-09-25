<?php

namespace Khazhinov\LaravelLighty\OpenApi\Complexes;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use JsonException;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload\IndexActionRequestPayloadFilterOperatorEnum;
use Khazhinov\LaravelLighty\OpenApi\Complexes\IndexAction\IndexActionArgumentsDTO;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\ModelReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessCollectionResourceResponse;

class IndexActionComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws JsonException
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new IndexActionArgumentsDTO($arguments);
        $model_reflector = new ModelReflector();
        $complex_result = new ComplexFactoryResult();
        $complex_result->parameters = [];

        if ($arguments->options->filters->enable) {
            $operator_enum_cases = IndexActionRequestPayloadFilterOperatorEnum::cases();
            $operator_enum_values = [];
            foreach ($operator_enum_cases as $enum_case) {
                $operator_enum_values[] = $enum_case->value;
            }

            $complex_result->request_body = RequestBody::create()->content(
                MediaType::json()->schema(
                    Schema::object('')->properties(
                        Schema::array('filter')->items(
                            Schema::object('')->properties(
                                Schema::string('column')
                                    ->enum(...$model_reflector->getFlattenModelProperties($arguments->model_class))
                                    ->description('Столбец сущности, по которому необходимо осуществить поиск')
                                    ->default($model_reflector->getFlattenModelProperties($arguments->model_class)[0]),
                                Schema::string('operator')
                                    ->enum(...$operator_enum_values)
                                    ->description('Столбец сущности, по которому необходимо осуществить поиск')
                                    ->default('='),
                                Schema::string('boolean')
                                    ->enum(...['and', 'or'])
                                    ->description('Логическая операция склеивания')
                                    ->default('and'),
                                Schema::string('value')
                                    ->description('Значение поля. Может быть массивом значений.'),
                            ),
                        ),
                    )
                ),
            );
        }

        if ($arguments->options->pagination->enable) {
            $complex_result->parameters[] = Parameter::query()
                ->name('limit')
                ->description('Количество элементов на странице')
                ->required(false)
                ->schema(Schema::integer()->default(10))
            ;
            $complex_result->parameters[] = Parameter::query()
                ->name('page')
                ->description('Требуемая страница')
                ->required(false)
                ->schema(Schema::integer()->default(1))
            ;
        }

        if ($arguments->options->orders->enable) {
            $complex_result->parameters[] = Parameter::query()
                ->name('order[]')
                ->description("Массив сортировок")
                ->required(false)
                ->schema(Schema::array()->items(Schema::string())->default(['-id']))
            ;
        }

        $complex_result->responses = [
            SuccessCollectionResourceResponse::build(
                item: $model_reflector->getSchemaForCollection($arguments->model_class, $arguments->collection_resource),
                is_pagination_enable: $arguments->options->pagination->enable
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
