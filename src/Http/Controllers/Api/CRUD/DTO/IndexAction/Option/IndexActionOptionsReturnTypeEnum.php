<?php

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

enum IndexActionOptionsReturnTypeEnum: string
{
    case Resource = 'resource';
    case Export = 'export';
}
