<?php

namespace Khazhinov\LaravelLighty\Http\Requests;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use RuntimeException;
use Spatie\DataTransferObject\Attributes\CastWith;

class DTOReflector
{
    /**
     * @param string $attribute_name
     * @param string $dto_class
     * @return array<string,mixed>
     * @throws ReflectionException
     */
    public function generateRequestValidation(string $attribute_name, string $dto_class): array
    {
        if (class_exists($dto_class)) {
            $reflection_class = new ReflectionClass($dto_class);
            $required = true;
            $nullable = false;
            $validation_array = [];

            $validation_array[$attribute_name] =
                [
                    "sometimes",
                    'array',
                    "nullable",
                ];


            foreach ($reflection_class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $property_name = $property->getName();
                /** @var ReflectionNamedType $property_type */
                $property_type = $property->getType();

                if ($property_type->allowsNull()) {
                    $required = false;
                    $nullable = true;
                }

                /** @var ReflectionNamedType $type */
                $type = $property_type->getName();

                if ($type == 'int') {
                    $type = 'numeric';
                } elseif (is_a($type, 'Khazhinov\PhpSupport\DTO\DataTransferObject', true)) {
                    $type = 'array';
                }

                $rule = [
                    $required ? "required" : "sometimes",
                    $type,
                    $nullable ? "nullable" : null,
                ];

                $rule = $this->removeNullValues($rule);

                $attribute_key = "$attribute_name.$property_name";

                if ($property_type->getName() === "array") {
                    $doc_comment = $property->getDocComment();
                    if ($doc_comment && preg_match('/@var\s+string\[\]/', $doc_comment)) {
                        $rule_string = ["sometimes", "string", "nullable"];
                        $validation_array[$attribute_key . '.*'] = $rule_string;
                    }

                    $attributes = $property->getAttributes();
                    if (! empty($attributes)) {
                        $validation_array[$attribute_key . '.*'] = $rule;
                        foreach ($attributes as $attribute) {
                            /** @var CastWith $instance */
                            $instance = $attribute->newInstance();
                            $item_type = $instance->args;
                            $rule_recursive = $this->generateRequestValidation("$attribute_name.$property_name.*", $item_type['itemType']);
                            $validation_array = array_merge($validation_array, $rule_recursive);
                        }
                    }
                } elseif (is_a($property_type->getName(), 'Khazhinov\PhpSupport\DTO\DataTransferObject', true)) {
                    $rule_recursive = $this->generateRequestValidation($attribute_key, $property_type->getName());
                    $validation_array = array_merge($validation_array, $rule_recursive);
                }

                $validation_array[$attribute_key] = $rule;
            }

            ksort($validation_array);

            return $validation_array;
        } else {
            throw new RuntimeException(sprintf('Class %s does not exist.', $dto_class));
        }
    }

    /**
     * @param array<string|int, mixed> $array
     * @return array<string|int, mixed>
     */
    public function removeNullValues(array $array): array
    {
        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }
}
