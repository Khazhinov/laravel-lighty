<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\SetPositionAction\Option;

use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\BaseCRUDOptionDTO;

class SetPositionActionOptionsDTO extends BaseCRUDOptionDTO
{
    public string $position_column = 'position';
}
