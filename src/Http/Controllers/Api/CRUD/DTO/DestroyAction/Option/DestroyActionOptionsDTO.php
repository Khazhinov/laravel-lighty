<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\DestroyAction\Option;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerOptionDTO;

class DestroyActionOptionsDTO extends ApiCRUDControllerOptionDTO
{
    public bool $force = false;
}
