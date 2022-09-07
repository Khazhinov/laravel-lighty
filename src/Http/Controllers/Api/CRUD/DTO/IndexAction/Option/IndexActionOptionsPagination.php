<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\LaravelLighty\DTO\DataTransferObject;

class IndexActionOptionsPagination extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;
}
