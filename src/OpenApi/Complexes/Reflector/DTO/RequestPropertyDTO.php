<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\DTO;

use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\SchemeTypeEnum;
use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class RequestPropertyDTO extends DataTransferObject
{
    public string $name;
    public ?string $description = null;
    public bool $required = false;
    public bool $nullable = false;
    public bool $sometimes = false;

    /** @var array<mixed>|null */
    public ?array $child = null;

    #[CastWith(EnumCaster::class, enumType: SchemeTypeEnum::class)]
    public SchemeTypeEnum $type;
}
