<?php

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

enum IndexActionOptionsReturnTypeEnum: string
{
//    case Resource = 'resource';
    case XLSX = 'xlsx';
    case CSV = 'csv';
    case UNDEFINED = 'undefined';
}
