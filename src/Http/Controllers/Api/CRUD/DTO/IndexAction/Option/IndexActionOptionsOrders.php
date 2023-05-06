<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Option;

use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class IndexActionOptionsOrders extends DataTransferObject
{
    /**
     * @var bool
     */
    public bool $enable = true;

    /**
     * @var array<string>
     */
    public array $default_orders = ['-id'];

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
    #[CastWith(EnumCaster::class, enumType: IndexActionOptionsOrdersNullPositionEnum::class)]
    public IndexActionOptionsOrdersNullPositionEnum $null_position = IndexActionOptionsOrdersNullPositionEnum::First;
}
