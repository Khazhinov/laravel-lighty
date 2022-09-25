<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\PhpSupport\DTO\DataTransferObject;

class IndexActionOptionsOrders extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;
}
