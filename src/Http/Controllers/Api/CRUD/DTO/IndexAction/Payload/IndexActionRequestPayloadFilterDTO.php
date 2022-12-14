<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload;

use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;

class IndexActionRequestPayloadFilterDTO extends DataTransferObject
{
    /**
     * @var IndexActionRequestPayloadFilterTypeEnum
     */
    #[CastWith(EnumCaster::class, enumType: IndexActionRequestPayloadFilterTypeEnum::class)]
    public IndexActionRequestPayloadFilterTypeEnum $type = IndexActionRequestPayloadFilterTypeEnum::Single;

    /**
     * @var IndexActionRequestPayloadFilterDTO[]
     */
    #[CastWith(ArrayCaster::class, itemType: IndexActionRequestPayloadFilterDTO::class)]
    public array $group = [];

    /**
     * @var ?string
     */
    public ?string $column = null;

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
