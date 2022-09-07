<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO\Helpers;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

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
            /** @var ReflectionNamedType|null $reflection_property_type */
            $reflection_property_type = $reflection_property->getType();
            if ($reflection_property_type) {
                $reflection_property_type_class = $reflection_property_type->getName();

                if (is_a($reflection_property_type_class, DataTransferObject::class, true)) {
                    $result_properties[$reflection_property->getName()] = [];
                }
            }
        }

        return $result_properties;
    }
}
