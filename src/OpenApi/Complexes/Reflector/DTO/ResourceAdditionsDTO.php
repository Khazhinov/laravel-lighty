<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\DTO;

use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ArrayOfScalar;
use Khazhinov\PhpSupport\Enums\ScalarTypeEnum;

class ResourceAdditionsDTO extends DataTransferObject
{
    /**
     * @var array<string>
     */
    #[ArrayOfScalar(ScalarTypeEnum::String, true)]
    public array $relationships = [];

    /**
     * @var array<string>
     */
    #[ArrayOfScalar(ScalarTypeEnum::String, true)]
    public array $properties = [];
}
