<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\IndexAction\Payload;

use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;

class IndexActionRequestPayloadExportExpandDTO extends DataTransferObject
{
    /**
     * @var ?string
     */
    public ?string $file_name = null;

    /**
     * @var IndexActionRequestPayloadExportDTO[]
     */
    #[CastWith(ArrayCaster::class, itemType: IndexActionRequestPayloadExportDTO::class)]
    public array $fields = [];
}
