<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload;

use Khazhinov\LaravelLighty\DTO\Custer\EnumCaster;
use Khazhinov\LaravelLighty\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class IndexActionRequestPayloadFilterDTO extends DataTransferObject
{
    /**
     * @var string
     */
    public string $column;

    /**
     * @var IndexActionRequestPayloadFilterOperatorEnum
     */
    #[CastWith(EnumCaster::class, enumType: IndexActionRequestPayloadFilterOperatorEnum::class)]
    public IndexActionRequestPayloadFilterOperatorEnum $operator = IndexActionRequestPayloadFilterOperatorEnum::Equal;

    /**
     * @var mixed
     */
    public mixed $value = null;

    /**
     * @var IndexActionRequestPayloadFilterBooleanEnum
     */
    #[CastWith(EnumCaster::class, enumType: IndexActionRequestPayloadFilterBooleanEnum::class)]
    public IndexActionRequestPayloadFilterBooleanEnum $boolean = IndexActionRequestPayloadFilterBooleanEnum::And;
}
