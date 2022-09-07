<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Enums;

enum ScalarTypeEnum: string
{
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case String = 'string';
}
