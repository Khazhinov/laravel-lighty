<?php

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector;

enum SchemeTypeEnum: string
{
    case String = 'string';
    case Number = 'number';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Single = 'object';
    case Collection = 'array';
}
