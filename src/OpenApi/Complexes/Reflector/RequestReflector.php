<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector;

use Exception;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\DTO\RequestPropertyDTO;
use ReflectionException;
use RuntimeException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class RequestReflector
{
    /**
     * @param  array<string, mixed>  $validation_request_rules
     * @param  array<string, string>  $validation_docs
     * @return array<string, RequestPropertyDTO>
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    protected function extractPropertiesFromRules(array $validation_request_rules, array $validation_docs = []): array
    {
        $result_properties = [];
        $array_properties = [];
        $children = [];

        foreach ($validation_request_rules as $validation_request_rule_name => $validation_request_rule_body) {
            if (is_string($validation_request_rule_body)) {
                // В случае, если правила представлены в виде строки
                $validation_request_rule_body = explode('|', $validation_request_rule_body);
            }

            $need_create = true;
            foreach ($array_properties as $array_property_name) {
                if (str_starts_with($validation_request_rule_name, $array_property_name)) {
                    $need_create = false;
                    if (! array_key_exists($array_property_name, $children)) {
                        $children[$array_property_name] = [
                            'rules' => [],
                            'docs' => [],
                        ];
                    }

                    $key = str_replace($array_property_name . '.', '', $validation_request_rule_name);
                    $children[$array_property_name]['rules'][$key] = $validation_request_rule_body;
                    $children[$array_property_name]['docs'][$key] = $validation_docs[$validation_request_rule_name];
                }
            }

            if ($need_create) {
                $property = [
                    'name' => $validation_request_rule_name,
                    // Считаем, что тип по умолчанию - строка
                    'type' => SchemeTypeEnum::String,
                    'description' => $validation_docs[$validation_request_rule_name] ?? null,
                ];

                foreach ($validation_request_rule_body as $validation_request_rule_body_item) {
                    if (! is_string($validation_request_rule_body_item)) {
                        continue;
                    }

                    // В случае, если правило имеет параметры
                    $exploded_validation_request_rule_body_item = explode(':', $validation_request_rule_body_item);
                    $current_validation_rule = $exploded_validation_request_rule_body_item[0];

                    switch ($current_validation_rule) {
                        case 'string':
                            $property['type'] = SchemeTypeEnum::String;

                            break;
                        case 'boolean':
                            $property['type'] = SchemeTypeEnum::Boolean;

                            break;
                        case 'integer':
                            $property['type'] = SchemeTypeEnum::Integer;

                            break;
                        case 'numeric':
                            $property['type'] = SchemeTypeEnum::Number;

                            break;
                        case 'array':
                            if (! in_array($validation_request_rule_name, $array_properties)) {
                                $array_properties[] = $validation_request_rule_name;
                            }

                            if (array_key_exists($validation_request_rule_name.'.*', $validation_request_rules)) {
                                $property['type'] = SchemeTypeEnum::Collection;
                            } else {
                                $property['type'] = SchemeTypeEnum::Single;
                            }

                            break;
                        case 'required':
                            $property['required'] = true;

                            break;
                        case 'nullable':
                            $property['nullable'] = true;

                            break;
                        case 'sometimes':
                            $property['sometimes'] = true;

                            break;
                    }
                }

                $result_properties[$validation_request_rule_name] = new RequestPropertyDTO($property);
            }
        }

        foreach ($children as $base_name => $child) {
            $result_properties[$base_name]->child = $this->extractPropertiesFromRules($child['rules'], $child['docs']);
        }

        return $result_properties;
    }

    /**
     * @param  string  $request_class
     * @return RequestPropertyDTO[]
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws Exception
     */
    public function getRequestProperties(string $request_class): array
    {
        if (! is_a($request_class, BaseRequest::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс запроса (%s) не имеет родительского класса %s', $request_class, BaseRequest::class));
        }

        $validation_request = new $request_class();
        $validation_request_rules = $validation_request->rules();
        $validation_docs = generate_doc_request_by_request_class($request_class);

        return $this->extractPropertiesFromRules($validation_request_rules, $validation_docs);
    }

    /**
     * @param  string  $request_class
     * @return SchemaContract[]
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function getSchemaByRequest(string $request_class): array
    {
        $request_properties = $this->getRequestProperties($request_class);

        return $this->makeSchemaProperties($request_properties);
    }

    /**
     * @param  null|array<string, RequestPropertyDTO>  $request_properties
     * @return SchemaContract[]
     */
    protected function makeSchemaProperties(?array $request_properties): array
    {
        $schema_properties = [];

        if (is_null($request_properties)) {
            $schema_properties[] = Schema::string('any_custom_data')
                ->nullable(true)
                ->description('В теле родителя может быть указано что угодно')
            ;

            return $schema_properties;
        }

        foreach ($request_properties as $property_name => $property_body) {
            $schema_type = $property_body->type->value;
            if ($property_body->type == SchemeTypeEnum::Collection || $property_body->type == SchemeTypeEnum::Single) {
                if ($property_body->type == SchemeTypeEnum::Collection) {
                    $schema_properties[] = Schema::array($property_body->name)
                        ->nullable($property_body->nullable)
                        ->description($property_body->description)
                        ->items(...$this->makeSchemaProperties($property_body->child))
                    ;
                } else {
                    $schema_properties[] = Schema::object($property_body->name)
                        ->nullable($property_body->nullable)
                        ->description($property_body->description)
                        ->properties(...$this->makeSchemaProperties($property_body->child))
                    ;
                }
            } else {
                $schema_properties[] = Schema::$schema_type($property_body->name)
                    ->nullable($property_body->nullable)
                    ->description($property_body->description)
                ;
            }
        }

        return $schema_properties;
    }
}
