<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\DestroyAction\Option;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BaseCRUDOptionDTO;

class DestroyActionOptionsDTO extends BaseCRUDOptionDTO
{
    public bool $force = false;
}
