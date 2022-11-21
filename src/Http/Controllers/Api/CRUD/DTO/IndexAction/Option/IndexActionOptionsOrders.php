<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\PhpSupport\DTO\DataTransferObject;

class IndexActionOptionsOrders extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;

    /**
     * Флаг для включения логики контроля положения полей со значением null
     *
     * @var bool
     */
    public bool $null_control = false;

    /**
     * Положение полей со значением null
     * По умолчанию - вверху
     *
     * @var IndexActionOptionsOrdersNullPositionEnum
     */
    public IndexActionOptionsOrdersNullPositionEnum $null_position = IndexActionOptionsOrdersNullPositionEnum::First;
}
