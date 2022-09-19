<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Helpers;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * @template T of object
 */
class DTOHelper
{
    /**
     * @param  string  $dto_class
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public static function getBaseConstructorBodyByDTOClass(string $dto_class): array
    {
        /** @var class-string<T>|T $dto_class */
        $reflector = new ReflectionClass($dto_class);
        $reflection_properties = $reflector->getProperties(ReflectionProperty::IS_PUBLIC);
        $result_properties = [];
        foreach ($reflection_properties as $reflection_property) {
            /** @var ReflectionNamedType|ReflectionUnionType|null $reflection_property_type */
            $reflection_property_type = $reflection_property->getType();
            if ($reflection_property_type) {
                if ($reflection_property_type instanceof ReflectionUnionType) {
                    $reflection_property_types = $reflection_property_type->getTypes();

                    $has_null_definition = false;
                    $result_definition = null;
                    foreach ($reflection_property_types as $reflection_property_type) {
                        $reflection_property_type_class = $reflection_property_type->getName();
                        if ($reflection_property_type_class === "null") {
                            $has_null_definition = true;

                            break;
                        }

                        if (is_a($reflection_property_type_class, DataTransferObject::class, true)) {
                            $result_definition = $reflection_property->getName();
                        }
                    }

                    if (! $has_null_definition && ! is_null($result_definition)) {
                        $result_properties[$result_definition] = [];
                    }
                } elseif (! $reflection_property_type->allowsNull()) {
                    $reflection_property_type_class = $reflection_property_type->getName();
                    if (is_a($reflection_property_type_class, DataTransferObject::class, true)) {
                        $result_properties[$reflection_property->getName()] = [];
                    }
                }
            }
        }

        return $result_properties;
    }
}
