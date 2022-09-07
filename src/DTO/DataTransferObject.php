<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\DTO;

use Khazhinov\LaravelLighty\DTO\Helpers\DTOHelper;
use ReflectionException;
use Spatie\DataTransferObject\DataTransferObject as BaseDTO;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

abstract class DataTransferObject extends BaseDTO
{
    /**
     * @param  mixed  ...$args
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function __construct(...$args)
    {
        if (is_array($args[0] ?? null)) {
            $args = $args[0];
        }

        $base_constructor_body = DTOHelper::getBaseConstructorBodyByDTOClass(static::class);
        $result_construct_body = array_merge_recursive_distinct($base_constructor_body, $args);

        parent::__construct($result_construct_body);
    }
}
