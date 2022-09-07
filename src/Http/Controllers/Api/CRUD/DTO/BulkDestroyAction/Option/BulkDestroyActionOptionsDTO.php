<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BulkDestroyAction\Option;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerOptionDTO;

class BulkDestroyActionOptionsDTO extends ApiCRUDControllerOptionDTO
{
    public bool $force = false;
}
